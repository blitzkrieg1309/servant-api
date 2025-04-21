<?php

namespace app\models;

use Yii;
use app\models\Users; // Ensure that the Users class exists in this namespace

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property string|null $user_id
 * @property string|null $order_date
 * @property string|null $status
 * @property float $total_price
 * @property bool|null $is_deleted
 * @property string|null $deleted_at
 *
 * @property OrderItems[] $orderItems
 * @property Users $user
 */
class Orders extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'string'],
            [['order_date', 'deleted_at'], 'safe'],
            [['total_price'], 'required'],
            [['total_price'], 'number'],
            [['is_deleted'], 'boolean'],
            [['status'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_date' => 'Order Date',
            'status' => 'Status',
            'total_price' => 'Total Price',
            'is_deleted' => 'Is Deleted',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['order_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}