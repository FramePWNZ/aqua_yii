<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payer_connection".
 *
 * @property int $id
 * @property int $user_id
 * @property int $payer_id
 */
class PayerConnection extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payer_connection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'payer_id'], 'required'],
            [['user_id', 'payer_id'], 'integer'],
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
            'payer_id' => 'Payer ID',
        ];
    }


    public static function findByPayerId($id, $childId)
    {
        return static::find()->where(['AND', ['payer_id' => $id], ['user_id' => $childId]])->one();
    }
}
