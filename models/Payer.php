<?php

namespace app\models;

use Yii;
use app\models\Children;

/**
 * This is the model class for table "payer".
 *
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string $b_date
 * @property string $mail
 * @property string $client
 * @property string $comment
 * @property int $delete_payer
 * @property int $date_create
 * @property string $job
 */
class Payer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'payer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['b_date', 'job'], 'required'],
            [['comment', 'job'], 'string'],
            [['delete_payer', 'date_create'], 'integer'],
            [['name', 'phone', 'mail', 'client'], 'string', 'max' => 255],
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
            'phone' => 'Phone',
            'b_date' => 'B Date',
            'mail' => 'Mail',
            'client' => 'Client',
            'comment' => 'Comment',
            'delete_payer' => 'Delete Payer',
            'date_create' => 'Date Create',
            'job' => 'Job',
        ];
    }

    public static function findByPhone($phone)
    {
        return static::find()->where(['AND', ['phone' => $phone], ['delete_payer' => 0]])->one();
    }


    public function getChilds()
    {
        return $this->hasMany(Children::className(), ['id' => 'user_id'])->viaTable('payer_connection', ['payer_id' => 'id'])->where(['delete_user' => 0]);
    }

}
