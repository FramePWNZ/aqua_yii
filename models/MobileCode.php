<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mobile_code".
 *
 * @property integer $id
 * @property string $phone
 * @property string $code
 * @property integer $confirmed
 * @property integer $try
 * @property integer $time_try
 */
class MobileCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mobile_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['confirmed', 'try', 'time_try'], 'required'],
            [['confirmed', 'try', 'time_try'], 'integer'],
            [['phone', 'code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'code' => 'Code',
            'confirmed' => 'Confirmed',
            'try' => 'Try',
            'time_try' => 'Time Try',
        ];
    }
}
