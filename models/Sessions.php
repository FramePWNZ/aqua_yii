<?php

namespace app\models;

use Yii;
use app\models\Projects;

/**
 * This is the model class for table "sessions".
 *
 * @property string $id
 * @property integer $uid
 * @property string $date_created
 * @property string $token
 */
class Sessions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sessions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'date_created'], 'required'],
            [['id', 'uid'], 'integer'],
            [['date_created'], 'safe'],
            [['token'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'date_created' => 'Date Created',
            'token' => 'Token'
        ];
    }
}
