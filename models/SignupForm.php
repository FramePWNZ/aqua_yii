<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class SignupForm extends Model
{
    public $login;
	public $phone;
    private $_user = false;
	
	public function __construct() {
	}

    /**
     * @return array the validation rules.
     */
    public function rules()
    {		
        return [
            // name and password are both required
            [['phone'], 'required', 'message'=>'Пожалуйста, заполните поле {attribute}.'],
        ];
    }
	
	/**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
			'phone' => 'Телефон'
        ];
    }
	
	/**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
			$user->phone = $this->phone;
            //$user->generateEmailConfirmToken();
			
            if ($user->save()) {
				return Yii::$app->user->login($user, 3600*24*30);
                return $user;
            }
        }
 
        return null;
    }	
	
	
	public function signupMobile()
    {
        if ($this->validate()) {
            $user = new User();
			$user->phone = $this->phone;
            if ($user->save()) {
				return true;
            }
        }
 
        return null;
    }	
	
	
	
	
	
}