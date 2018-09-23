<?php

namespace app\models;

use Yii;
use app\models\Cabinet;

/**
 * This is the model class for table "address_cab".
 *
 * @property int $id
 * @property string $name
 * @property string $short_name
 */
class AddressCab extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'address_cab';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'short_name'], 'string', 'max' => 255],
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
            'short_name' => 'Short Name',
        ];
    }


    public function getCabinets()
    {
        return $this->hasMany(Cabinet::className(), ['address' => 'id']);
    }

}
