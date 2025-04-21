<?php

namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\Cors;
use app\models\Product;
use yii\filters\auth\HttpBearerAuth;
use app\models\Orders;

class OrdersController extends ActiveController
{
    public $modelClass = 'app\models\Orders';

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
                'Origin' => ['http://localhost:4200', 'https://servant-store-e2x61pc5z-fatihs-projects-57414a3c.vercel.app/', 'https://servant-store.vercel.app'],
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
        ];
    }

    public function actionGet()
    {
        $orders = Orders::find()->all();
        $response = [];
        foreach ($orders as $order) {
            $response[] = [
                'order_id' => $order->id,
                'order_date' => $order->order_date,
                'product' => $order->product->name,
                'quantity' => $order->quantity,
                'total_price' => $order->total_price,
            ];
        }
        return $response;
    }

}