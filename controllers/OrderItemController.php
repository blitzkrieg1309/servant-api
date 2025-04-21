<?php

namespace app\controllers;

use app\models\OrderItems;
use yii\rest\ActiveController;
use yii\filters\Cors;
use app\models\Product;
use app\models\Orders;
use yii\filters\auth\HttpBearerAuth;
use Yii;

class OrderItemController extends ActiveController
{
    public $modelClass = 'app\models\OrderItems';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // authentikasi Bearer Token
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options']
        ];

        // Cors Filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:4200', 'https://servant-store-e2x61pc5z-fatihs-projects-57414a3c.vercel.app', 'https://servant-store.vercel.app'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Tambahkan handler untuk OPTIONS
        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
        ];

        return $actions;
    }

    public function verbs()
    {
        return [
            'index' => ['GET', 'OPTIONS'],
            'view' => ['GET', 'OPTIONS'],
            'create' => ['POST', 'OPTIONS'],
            'update' => ['PUT', 'OPTIONS'],
            'delete' => ['DELETE', 'OPTIONS'],
        ];
    }

    public function actionGetOrder()
    {
        $user_id = Yii::$app->user->identity->id;

        if (!$user_id) {
            return ['status' => 'error', 'message' => 'Unauthorized access'];
        }

        $items = OrderItems::find()
            ->joinWith('order')
            ->where(['orders.user_id' => $user_id])
            ->with('product')
            ->asArray()
            ->all();

        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item['id'],
                'order_id' => $item['order_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'product' => [
                    'id' => $item['product']['id'],
                    'name' => $item['product']['name'],
                ],
                'order' => [
                    'status' => $item['order']['status'],
                    'order_date' => $item['order']['order_date'],
                ],
            ];
        }
         
        return $result;
    }
}