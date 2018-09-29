<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "abonements_clients".
 *
 * @property int $id
 * @property int $abonement_id
 * @property int $user_id
 * @property string $date_from
 * @property string $date_to
 * @property int $count
 * @property int $count_buffer
 * @property int $days
 * @property int $old
 * @property int $cost
 * @property int $transfer
 * @property int $freezing
 * @property int $unlimited_freeze
 */
class AbonementsClients extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'abonements_clients';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['abonement_id', 'user_id', 'count', 'count_buffer', 'days', 'old', 'cost', 'transfer', 'freezing', 'unlimited_freeze'], 'integer'],
            [['date_from', 'date_to'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'abonement_id' => 'Abonement ID',
            'user_id' => 'User ID',
            'date_from' => 'Date From',
            'date_to' => 'Date To',
            'count' => 'Count',
            'count_buffer' => 'Count Buffer',
            'days' => 'Days',
            'old' => 'Old',
            'cost' => 'Cost',
            'transfer' => 'Transfer',
            'freezing' => 'Freezing',
            'unlimited_freeze' => 'Unlimited Freeze',
        ];
    }
}
