<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "services_package".
 *
 * @property int $id
 * @property int $customer_record_id
 * @property int $service_id
 * @property int $count
 */
class ServicesPackage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'services_package';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_record_id', 'service_id', 'count'], 'required'],
            [['customer_record_id', 'service_id', 'count'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_record_id' => 'Customer Record ID',
            'service_id' => 'Service ID',
            'count' => 'Count',
        ];
    }
}
