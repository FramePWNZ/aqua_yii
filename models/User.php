<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
	public static function tableName()
    {
        return '{{%auth}}';
    }

    public static function findIdentity($id)
	{
		return static::findOne(['id' => $id]);
	}
	
	
	public static function findIdentityByAccessToken($token, $type = null)
	{
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}
	
	
	
	public static function findByUsername($username)
	{
		return static::findOne(['username' => $username]);
	}
	
	
	
	public function getId()
	{
		return $this->getPrimaryKey();
	}
	public function getAuthKey()
	{
		return $this->auth_key;
	}    
	public function validateAuthKey($authKey)
	{
		return $this->getAuthKey() === $authKey;
	}
	public function validatePassword($password)
	{
		return Yii::$app->security->validatePassword($password, $this->password);
	}

	public function getUserRole() {
	    if(\Yii::$app->user->identity) {
            return key(\Yii::$app->authManager->getRolesByUser(\Yii::$app->user->identity->id));
        } else {
	        return false;
        }
    }
	
	public static function findByPhone($phone)
	{
		return static::findOne(['phone' => $phone]);
	}
	
	public static function findByPhoneOrLogin($name)
	{
		return static::find()->where(['OR', ['phone' => $name], ['username' => $name]])->one();
	}
	
	public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }
	
	public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}
