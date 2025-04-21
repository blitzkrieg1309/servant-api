<?php

namespace app\controllers;

use Yii;
use app\models\Cart;
use app\models\Product;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\filters\Cors;
use yii\filters\auth\HttpBearerAuth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use yii\web\UnauthorizedHttpException;
use app\models\Orders;
use app\models\OrderItems;


class CartController extends ActiveController
{
    public $modelClass = 'app\models\Cart';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS configuration
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:4200', 'https://servant-store-e2x61pc5z-fatihs-projects-57414a3c.vercel.app', 'https://servant-store.vercel.app'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS','DELETE'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        // Bearer Token Auth
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
        ];
        return $actions;
    }

    public function verbs()
    {
        return [
            'add-to-cart' => ['POST', 'OPTIONS'],
            'get-cart-with-product' => ['GET', 'OPTIONS'],
            'delete-cart-item-by-product-id' => ['DELETE', 'OPTIONS'],
            'checkout' => ['POST', 'OPTIONS'],
        ];
    }

    public function actionAddToCart()
    {
        $request = Yii::$app->request;
        $bodyParams = json_decode($request->getRawBody(), true);

        // Extract user ID from authenticated token
        $user_id = Yii::$app->user->identity->id;

        if (!$user_id) {
            throw new UnauthorizedHttpException('User ID is required.');
        }

        // Validate required parameters
        $product_id = $bodyParams['product_id'] ?? null;
        $quantity = $bodyParams['quantity'] ?? 1;

        if (!$product_id) {
            throw new BadRequestHttpException('Product ID is required.');
        }

        // Validate product existence
        $product = Product::findOne($product_id);
        if (!$product || $product->stock < $quantity) {
            throw new BadRequestHttpException('Invalid product or insufficient stock.');
        }

        // Check and update existing cart item
        $cartItem = Cart::findOne(['user_id' => $user_id, 'product_id' => $product_id]);
        if ($cartItem) {
            $cartItem->quantity += $quantity;
            if ($cartItem->save()) {
                return ['status' => true, 'message' => 'Item quantity updated in cart.'];
            }
            throw new BadRequestHttpException('Failed to update item in cart.');
        }

        // Add new cart item
        $cart = new Cart();
        $cart->user_id = $user_id;
        $cart->product_id = $product_id;
        $cart->quantity = $quantity;

        if ($cart->save()) {
            return ['status' => true, 'message' => 'Item added to cart successfully.'];
        }
        throw new BadRequestHttpException('Failed to add item to cart.');
    }

    public function actionGetCartWithProduct()
    {
        $user_id = Yii::$app->user->identity->id;

        if (!$user_id) {
            throw new UnauthorizedHttpException('User ID is required.');
        }

        return Cart::find()
            ->where(['user_id' => $user_id])
            ->with('product')
            ->asArray()
            ->all();
    }

    public function actionDeleteCartItemByProductId($product_id)
    {
        $user_id = Yii::$app->user->identity->id;

        $cartItem = Cart::findOne(['user_id' => $user_id, 'product_id' => $product_id]);
        if (!$cartItem) {
            throw new BadRequestHttpException('Cart item not found.');
        }

        if ($cartItem->user_id !== Yii::$app->user->identity->id) {
            throw new UnauthorizedHttpException('You are not authorized to delete this cart item.');
        }

        if ($cartItem->delete()) {
            return ['status' => true, 'message' => 'Cart item deleted successfully.'];
        }

        return ['status' => false, 'message' => 'Failed to delete cart item.'];
    }

    public function actionCheckout()
    {
        $user_id = Yii::$app->user->identity->id;

        if (!$user_id) {
            throw new UnauthorizedHttpException('User ID is required.');
        }

        // fetch cart items
        $cartItems = Cart::find()
            ->where(['user_id' => $user_id])
            ->with('product')
            ->all();
        
        if (empty($cartItems)) {
            throw new BadRequestHttpException('No items found in cart.');
        }

        // calculate total amount
        $totalAmount = 0;
        foreach ($cartItems as $cartItem){
            if($cartItem->product->stock < $cartItem-> quantity) {
                throw new BadRequestHttpException(
                    'Insufficient stock for product: ' . $cartItem->product->name
                );
            }
            $totalAmount += $cartItem->product->price * $cartItem->quantity;
        }
        
        // create order
        $order = new Orders();
        $order->user_id = $user_id;
        $order->total_price = $totalAmount;

        if(!$order->save()) {
            throw new BadRequestHttpException('Failed to create order.');
        }

        // Create order items and update stock
        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItems();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $cartItem->product_id;
            $orderItem->quantity = $cartItem->quantity;
            $orderItem->price = $cartItem->product->price;

            if(!$orderItem->save()) {
                throw new BadRequestHttpException('Failed to create order items.');
            }
    
            // Update product stock
            $product = $cartItem->product;
            $product->stock -= $cartItem->quantity;
    
            if(!$product->save()) {
                throw new BadRequestHttpException('Failed to update product stock.');
            }

            // delete cart items
            $cartItem->delete();
        }

        return [
            'status' => true,
            'message' => 'Order placed successfully.',
            'order_id' => $order->id,
        ];
        
    }

    
}