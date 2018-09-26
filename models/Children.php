<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int $status
 * @property string $login
 * @property string $password
 * @property string $name
 * @property string $b_date
 * @property string $phone
 * @property string $mail
 * @property string $note
 * @property string $discont
 * @property int $rights_id
 * @property int $delete_user
 * @property int $client_status_id
 * @property string $status_change_date
 * @property int $user_private_status
 * @property int $first_date
 * @property int $last_date
 * @property string $client_balance
 * @property string $info_source_id
 * @property string $attends_id
 * @property int $studio_id
 * @property int $alert_id
 * @property int $exercise_count
 * @property string $rate
 * @property string $created
 * @property string $rate_shift
 * @property int $operator_number
 * @property int $operator
 * @property int $phone_confirm
 */
class Children extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'rights_id', 'delete_user', 'client_status_id', 'user_private_status', 'first_date', 'last_date', 'studio_id', 'alert_id', 'exercise_count', 'operator_number', 'operator', 'phone_confirm'], 'integer'],
            [['name', 'phone'], 'required'],
            [['status_change_date', 'created'], 'safe'],
            [['note'], 'string'],
            [['discont', 'client_balance', 'rate', 'rate_shift'], 'number'],
            [['login', 'password', 'phone', 'mail'], 'string', 'max' => 366],
            [['name'], 'string', 'max' => 566],
            [['info_source_id', 'attends_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'login' => 'Login',
            'password' => 'Password',
            'name' => 'Name',
            'b_date' => 'B Date',
            'phone' => 'Phone',
            'mail' => 'Mail',
            'note' => 'Note',
            'discont' => 'Discont',
            'rights_id' => 'Rights ID',
            'delete_user' => 'Delete User',
            'client_status_id' => 'Client Status ID',
            'status_change_date' => 'Status Change Date',
            'user_private_status' => 'User Private Status',
            'first_date' => 'First Date',
            'last_date' => 'Last Date',
            'client_balance' => 'Client Balance',
            'info_source_id' => 'Info Source ID',
            'attends_id' => 'Attends ID',
            'studio_id' => 'Studio ID',
            'alert_id' => 'Alert ID',
            'exercise_count' => 'Exercise Count',
            'rate' => 'Rate',
            'created' => 'Created',
            'rate_shift' => 'Rate Shift',
            'operator_number' => 'Operator Number',
            'operator' => 'Operator',
            'phone_confirm' => 'Phone Confirm',
        ];
    }

    public function getPayersConnections()
    {
        return $this->hasMany(PayerConnection::className(), ['id' => 'user_id']);
    }
}
