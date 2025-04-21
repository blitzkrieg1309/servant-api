<?php

namespace app\controllers;

use yii;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;
use yii\rest\Controller;
use app\models\User;
use Firebase\JWT\JWT;

class LoginController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Format response JSON
        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        // Add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:4200', 'https://servant-store-e2x61pc5z-fatihs-projects-57414a3c.vercel.app', 'https://servant-store.vercel.app'],
                'Access-Control-Request-Method' => ['GET', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        return $behaviors;
    }

    public function verbs()
    {
        return [
            'login' => ['POST', 'OPTIONS'],
        ];
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        
        $bodyParams = json_decode($request->getRawBody(), true);
        $email = $bodyParams['email']?? null;
        $password = $bodyParams['password']?? null;

        if(!$email || !$password) {
            throw new UnauthorizedHttpException('Password harus diisi !');
        }

        $user = User::find()
            ->where(['or', ['email' => $email], ['name' => $email]])
            ->one();

        if (!$user || !Yii::$app->getSecurity()->validatePassword($password, $user->password)) {
            throw new UnauthorizedHttpException('Email atau password salah !');
        }

        // jwt
        $key = 'securekey123';
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'username' => $user->name,
            'iat' => time(),
            'exp' => time() + 3600, //token berlaku 1 jam
            'user' => [
                'id' => $user->id,
                'username' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];

        // generate jwt token

        $token = JWT::encode($payload, $key, 'HS256');

        return [
            'status' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ];
    }
}