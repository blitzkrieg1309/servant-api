<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'iO5xoqClt-x5EOORU5CagV6a9g6H0ybF',
            
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'], // Tambahkan 'info' untuk menangkap lebih detail
                    'logFile' => '@runtime/logs/app.log',
                    'logVars' => ['_GET', '_POST'], // Menangkap request payload
                ],
            ],
        ],
        'db' => $db,
        
            'response' => [
                'format' => yii\web\Response::FORMAT_JSON,
            ],
            'urlManager' => [
                'enablePrettyUrl' => true,
                'showScriptName' => false,
                'enableStrictParsing' => true,
                'rules' => [
                    // Register and Login
                    'POST api/register' => 'register/register',
                    'OPTIONS api/register' => 'register/register',
                    'POST api/login' => 'login/login',
                    'OPTIONS api/login' => 'login/login',
                
                    // Product Endpoints
                    'GET api/servants/id/<id>' => 'product/view',
                    'OPTIONS api/servants/id/<id>' => 'product/options',
                    'GET api/servants/class/<class>' => 'product/class',
                    'OPTIONS api/servants/class/<class>' => 'product/class',
                    'GET api/servants' => 'product/index',
                    'OPTIONS api/servants' => 'product/options',
                    'GET api/servants/all' => 'product/all',
                    'OPTIONS api/servants/all' => 'product/options',
                
                    // Cart Endpoints
                    'POST api/cart/add' => 'cart/add-to-cart',
                    'OPTIONS api/cart/add' => 'cart/options',
                    'GET api/cart' => 'cart/get-cart-with-product',
                    'OPTIONS api/cart' => 'cart/options',
                    'DELETE api/cart/delete/<product_id>' => 'cart/delete-cart-item-by-product-id',
                    'OPTIONS api/cart/delete/<product_id>' => 'cart/options',
                    'POST api/cart/checkout' => 'cart/checkout',
                    'OPTIONS api/cart/checkout' => 'cart/options',

                    // Orders Endpoints
                    'GET api/orders' => 'orders/get',
                    'OPTIONS api/orders' => 'orders/get',

                    // Order Items Endpoints
                    'GET api/order-item' => 'order-item/index',
                    'OPTIONS api/order-item' => 'order-item/options',
                    'GET api/order-item/order' => 'order-item/get-order',
                    'OPTIONS api/order-item/order' => 'order-item/options',

                    // User Endpoints
                    'GET api/user' => 'user/index',
                    'OPTIONS api/user' => 'user/options',
                    'GET api/user/<id>' => 'user/view',
                    'OPTIONS api/user/<id>' => 'user/options',

                    // Default REST Rules
                    ['class' => 'yii\rest\UrlRule', 'controller' => ['order-item','user', 'product', 'orders', 'register', 'login', 'cart']],
                ],
            ],



    ],
    
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;