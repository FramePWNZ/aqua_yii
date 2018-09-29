<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "abonements".
 *
 * @property int $id
 * @property string $title
 * @property int $days
 * @property int $cost
 * @property int $count
 * @property int $buffer_days
 * @property int $buffer_count
 * @property int $status
 * @property int $openbonus
 * @property int $transfer
 * @property int $freezing
 * @property int $unlimited_freeze
 * @property int $non_refundable_amount
 * @property int $amocrm
 */
class Abonements extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'abonements';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['days', 'cost', 'count', 'buffer_days', 'buffer_count', 'status', 'openbonus', 'transfer', 'freezing', 'unlimited_freeze', 'non_refundable_amount', 'amocrm'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'days' => 'Days',
            'cost' => 'Cost',
            'count' => 'Count',
            'buffer_days' => 'Buffer Days',
            'buffer_count' => 'Buffer Count',
            'status' => 'Status',
            'openbonus' => 'Openbonus',
            'transfer' => 'Transfer',
            'freezing' => 'Freezing',
            'unlimited_freeze' => 'Unlimited Freeze',
            'non_refundable_amount' => 'Non Refundable Amount',
            'amocrm' => 'Amocrm',
        ];
    }
}
