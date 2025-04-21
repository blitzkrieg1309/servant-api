<?php

namespace app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\User;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\filters\Cors;


class RegisterController extends Controller
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
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:4200', 'https://servant-store-e2x61pc5z-fatihs-projects-57414a3c.vercel.app', 'https://servant-store.vercel.app'],
                'Access-Control-Request-Method' => ['POST','OPTIONS'],
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
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
    
    // Add verbs() method to explicitly allow OPTIONS
    public function verbs()
    {
        return [
            'register' => ['POST', 'OPTIONS'],
        ];
    }


    public function actionRegister()
    {
        $request = Yii::$app->request;

        $bodyParams = json_decode($request->getRawBody(), true);
        $username = $bodyParams['username'] ?? null;
        $email = $bodyParams['email'] ?? null;
        $password = $bodyParams['password'] ?? null;

        if (!$username || !$email || !$password) {
            throw new BadRequestHttpException('Lengkapi data anda');
        }

        // Check if email is already registered
        if (User::isEmailRegistered($email)) {
            throw new BadRequestHttpException('Email sudah terdaftar');
        }

        
        $user = User::registerUser([
            'name' => $username,
            'email' => $email,
            'password' => $password
        ]);

        if ($user) {
            return [
                'status' => true,
                'message' => 'Registrasi berhasil',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ];
        } else {
            throw new BadRequestHttpException('Registrasi gagal');
        }
    }
}