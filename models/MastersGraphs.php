<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "masters_graphs".
 *
 * @property int $id
 * @property int $master_id
 * @property int $cab_id
 * @property string $date
 * @property string $comment
 * @property int $type
 * @property string $time_work
 */
class MastersGraphs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'masters_graphs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['master_id', 'cab_id', 'date', 'comment', 'type', 'time_work'], 'required'],
            [['master_id', 'cab_id', 'type'], 'integer'],
            [['date'], 'safe'],
            [['time_work'], 'string'],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'master_id' => 'Master ID',
            'cab_id' => 'Cab ID',
            'date' => 'Date',
            'comment' => 'Comment',
            'type' => 'Type',
            'time_work' => 'Time Work',
        ];
    }
}
