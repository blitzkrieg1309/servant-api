<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "users".
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $role
 * @property string|null $address
 * @property string|null $created_at
 *
 * @property Cart[] $carts
 * @property Orders[] $orders
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email', 'password'], 'required'],
            [['address'], 'string'],
            [['created_at'], 'safe'],
            [['name', 'email'], 'string', 'max' => 100],
            [['password'], 'string', 'max' => 255],
            [['role'], 'string', 'max' => 50],
            [['email'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'role' => 'Role',
            'address' => 'Address',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Fungsi untuk memeriksa apakah email sudah ada
     */
    public static function isEmailRegistered($email)
    {
        return self::findOne(['email' => $email]) ? true : false;
    }

    /**
     * Fungsi untuk mendaftarkan pengguna baru
     */
    public static function registerUser($data)
    {
        $user = new self();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Yii::$app->security->generatePasswordHash($data['password']);
        $user->role = 'user';
        $user->created_at = date('Y-m-d H:i:s');
        return $user->save() ? $user : null;
    }

    /**
     * Mengimplementasikan IdentityInterface::findIdentity()
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Mengimplementasikan IdentityInterface::findIdentityByAccessToken()
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $key = 'securekey123'; // Gunakan kunci yang sama seperti dalam encoding JWT
        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
            return static::findOne(['id' => $decoded->user_id]);
        } catch (\Exception $e) {
            return null; // Token tidak valid
        }
    }

    /**
     * Mengimplementasikan IdentityInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Mengimplementasikan IdentityInterface::getAuthKey()
     */
    public function getAuthKey()
    {
        return null; // Tidak digunakan untuk JWT
    }

    /**
     * Mengimplementasikan IdentityInterface::validateAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return false; // Tidak digunakan untuk JWT
    }
}