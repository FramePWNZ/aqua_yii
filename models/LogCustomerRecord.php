<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "log_customer_record".
 *
 * @property int $id
 * @property int $customer_record_id
 * @property string $user_change_name
 * @property string $date_of_change
 * @property string $cabinet_name
 * @property string $master_name
 * @property string $client_name
 * @property double $time_record
 * @property int $performed
 * @property int $deleted
 * @property int $user_change_id
 * @property int $cabinet_id
 * @property int $master_id
 * @property int $client_id
 * @property int $cert_number
 * @property string $duration
 * @property double $duration_for_count
 * @property string $date_record
 * @property int $discont_percent
 * @property string $amount_services
 * @property string $user_ip
 * @property string $user_browser
 */
class LogCustomerRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log_customer_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_record_id', 'user_change_name', 'date_of_change', 'cabinet_name', 'master_name', 'client_name', 'time_record', 'performed', 'user_change_id', 'cabinet_id', 'master_id', 'client_id', 'cert_number', 'duration', 'duration_for_count', 'date_record', 'user_ip', 'user_browser'], 'required'],
            [['customer_record_id', 'performed', 'deleted', 'user_change_id', 'cabinet_id', 'master_id', 'client_id', 'cert_number', 'discont_percent'], 'integer'],
            [['date_of_change', 'date_record'], 'safe'],
            [['time_record', 'duration_for_count', 'amount_services'], 'number'],
            [['user_change_name', 'cabinet_name', 'master_name', 'client_name', 'user_ip', 'user_browser'], 'string', 'max' => 255],
            [['duration'], 'string', 'max' => 11],
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
            'user_change_name' => 'User Change Name',
            'date_of_change' => 'Date Of Change',
            'cabinet_name' => 'Cabinet Name',
            'master_name' => 'Master Name',
            'client_name' => 'Client Name',
            'time_record' => 'Time Record',
            'performed' => 'Performed',
            'deleted' => 'Deleted',
            'user_change_id' => 'User Change ID',
            'cabinet_id' => 'Cabinet ID',
            'master_id' => 'Master ID',
            'client_id' => 'Client ID',
            'cert_number' => 'Cert Number',
            'duration' => 'Duration',
            'duration_for_count' => 'Duration For Count',
            'date_record' => 'Date Record',
            'discont_percent' => 'Discont Percent',
            'amount_services' => 'Amount Services',
            'user_ip' => 'User Ip',
            'user_browser' => 'User Browser',
        ];
    }
}
