<?php

namespace app\controllers;

use yii\rest\ActiveController;
use yii\filters\Cors;
use app\models\Product;
use yii\filters\auth\HttpBearerAuth;


class ProductController extends ActiveController
{
    public $modelClass = 'app\models\Product';

    public function behaviors()
    {
        
        $behaviors = parent::behaviors();

        // authentikasi Bearer Token
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['index','class','view','options','all'],
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
            'class' => ['GET', 'OPTIONS'],
            'filter-by-class' => ['GET', 'OPTIONS'],
            'options' => ['OPTIONS'],
            'all' => ['GET', 'OPTIONS'],
        ];
    }


    public function actionClass($class)
    {
        
        $products = Product::find()
        ->where(['class' => $class])
        ->asArray()
        ->all();

        if (empty($products)) {
            throw new \yii\web\NotFoundHttpException('No products found for the specified class.');
        }

        return $this->asJson($products);
    }

    public function actionAll() {
        $products = Product::find()->asArray()->all();
        return $this->asJson($products);
    }
}