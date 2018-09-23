<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cabinet".
 *
 * @property int $id
 * @property string $name
 * @property int $on_front
 * @property string $shirt_name
 * @property int $address
 */
class Cabinet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cabinet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'on_front', 'shirt_name'], 'required'],
            [['on_front', 'address'], 'integer'],
            [['name'], 'string', 'max' => 366],
            [['shirt_name'], 'string', 'max' => 10],
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
            'on_front' => 'On Front',
            'shirt_name' => 'Shirt Name',
            'address' => 'Address',
        ];
    }


    public function getAddressname()
    {
        return $this->hasOne(AddressCab::className(), ['id' => 'address']);
    }
}
