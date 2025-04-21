<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property float $price
 * @property int|null $stock
 * @property string|null $image_url
 * @property string|null $class
 * @property string|null $created_at
 * @property string|null $lore
 * @property string|null $skill
 * @property string|null $noble_phantasm
 * @property int|null $star
 *
 * @property Cart[] $carts
 * @property OrderItems[] $orderItems
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'price'], 'required'],
            [['id', 'description', 'lore', 'skill', 'noble_phantasm'], 'string'],
            [['price'], 'number'],
            [['stock', 'star'], 'default', 'value' => null],
            [['stock', 'star'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['image_url'], 'string', 'max' => 255],
            [['class'], 'string', 'max' => 50],
            [['id'], 'unique'],
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
            'description' => 'Description',
            'price' => 'Price',
            'stock' => 'Stock',
            'image_url' => 'Image Url',
            'class' => 'Class',
            'created_at' => 'Created At',
            'lore' => 'Lore',
            'skill' => 'Skill',
            'noble_phantasm' => 'Noble Phantasm',
            'star' => 'Star',
        ];
    }

    /**
     * Gets query for [[Carts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCarts()
    {
        return $this->hasMany(Cart::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[OrderItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItems()
    {
        return $this->hasMany(OrderItems::class, ['product_id' => 'id']);
    }
}