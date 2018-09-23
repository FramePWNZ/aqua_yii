<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "customer_record".
 *
 * @property int $id
 * @property int $cabinet_id
 * @property int $master_id
 * @property int $client_id
 * @property int $cert_number
 * @property string $duration
 * @property double $duration_for_count
 * @property string $date_record
 * @property double $time_record
 * @property int $performed
 * @property int $discont_percent
 * @property string $amount_services
 * @property int $garant
 * @property int $garant_perf
 * @property int $without_salary
 * @property int $pedicur
 * @property string $pedicur_from
 * @property string $pedicur_to
 * @property int $payment_type
 * @property int $phone_confirm
 * @property int $abonement_id
 */
class Record extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cabinet_id', 'master_id', 'client_id', 'cert_number', 'duration', 'duration_for_count', 'date_record', 'time_record', 'pedicur_from', 'pedicur_to'], 'required'],
            [['cabinet_id', 'master_id', 'client_id', 'cert_number', 'performed', 'discont_percent', 'garant', 'garant_perf', 'without_salary', 'pedicur', 'payment_type', 'phone_confirm', 'abonement_id'], 'integer'],
            [['duration_for_count', 'time_record', 'amount_services', 'pedicur_from', 'pedicur_to'], 'number'],
            [['date_record'], 'safe'],
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
            'cabinet_id' => 'Cabinet ID',
            'master_id' => 'Master ID',
            'client_id' => 'Client ID',
            'cert_number' => 'Cert Number',
            'duration' => 'Duration',
            'duration_for_count' => 'Duration For Count',
            'date_record' => 'Date Record',
            'time_record' => 'Time Record',
            'performed' => 'Performed',
            'discont_percent' => 'Discont Percent',
            'amount_services' => 'Amount Services',
            'garant' => 'Garant',
            'garant_perf' => 'Garant Perf',
            'without_salary' => 'Without Salary',
            'pedicur' => 'Pedicur',
            'pedicur_from' => 'Pedicur From',
            'pedicur_to' => 'Pedicur To',
            'payment_type' => 'Payment Type',
            'phone_confirm' => 'Phone Confirm',
            'abonement_id' => 'Abonement ID',
        ];
    }
}
