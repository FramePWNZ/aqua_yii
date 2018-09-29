<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_services_package".
 *
 * @property int $id
 * @property string $date_of_change
 * @property int $customer_record_id
 * @property int $customer_record_discont
 * @property string $customer_record_date
 * @property int $master_id
 * @property string $master_name
 * @property int $service_id
 * @property string $service_name
 * @property int $service_type
 * @property int $count
 * @property string $service_cost
 * @property string $full_price
 * @property int $revision_id
 * @property string $changer_name
 * @property int $client_id
 * @property string $client_name
 * @property int $cabinet_id
 * @property double $time_record
 * @property string $balance_confirm
 * @property string $cabinet_name
 * @property int $percent_services
 * @property int $percent_product
 * @property int $garant
 * @property int $change_garant
 * @property string $sert_nominal
 * @property int $change_salary
 * @property int $abonement
 */
class LogServicesPackage extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_services_package';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date_of_change', 'customer_record_id', 'customer_record_discont', 'customer_record_date', 'master_id', 'master_name', 'service_id', 'service_name', 'count', 'service_cost', 'full_price', 'changer_name', 'client_id', 'client_name', 'cabinet_id', 'time_record', 'balance_confirm', 'cabinet_name', 'garant', 'change_garant', 'sert_nominal', 'change_salary'], 'required'],
            [['date_of_change', 'customer_record_date'], 'safe'],
            [['customer_record_id', 'customer_record_discont', 'master_id', 'service_id', 'service_type', 'count', 'revision_id', 'client_id', 'cabinet_id', 'percent_services', 'percent_product', 'garant', 'change_garant', 'change_salary', 'abonement'], 'integer'],
            [['service_cost', 'full_price', 'time_record', 'balance_confirm', 'sert_nominal'], 'number'],
            [['master_name', 'service_name', 'changer_name', 'client_name', 'cabinet_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_of_change' => 'Date Of Change',
            'customer_record_id' => 'Customer Record ID',
            'customer_record_discont' => 'Customer Record Discont',
            'customer_record_date' => 'Customer Record Date',
            'master_id' => 'Master ID',
            'master_name' => 'Master Name',
            'service_id' => 'Service ID',
            'service_name' => 'Service Name',
            'service_type' => 'Service Type',
            'count' => 'Count',
            'service_cost' => 'Service Cost',
            'full_price' => 'Full Price',
            'revision_id' => 'Revision ID',
            'changer_name' => 'Changer Name',
            'client_id' => 'Client ID',
            'client_name' => 'Client Name',
            'cabinet_id' => 'Cabinet ID',
            'time_record' => 'Time Record',
            'balance_confirm' => 'Balance Confirm',
            'cabinet_name' => 'Cabinet Name',
            'percent_services' => 'Percent Services',
            'percent_product' => 'Percent Product',
            'garant' => 'Garant',
            'change_garant' => 'Change Garant',
            'sert_nominal' => 'Sert Nominal',
            'change_salary' => 'Change Salary',
            'abonement' => 'Abonement',
        ];
    }
}
