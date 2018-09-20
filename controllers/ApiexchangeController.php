<?php

namespace app\controllers;
error_reporting(E_ALL & ~E_NOTICE);

use Yii;
use app\models\Sessions;
use app\models\LoginForm;
use app\models\User;
use app\models\SignupForm;
use app\models\MobileCode;

class ApiexchangeController extends \yii\web\Controller
{
	
	//Проверка авторизации для проверки методов
	public function beforeAction($action)
	{		
		$_POST = $_REQUEST;
		
		setlocale (LC_TIME, "ru_RU");
		
		return parent::beforeAction($action);
	}	
	
    public function actionIndex()
    {
        return $this->render('index');
    }
	
	//Получить пользователя по токену
	public function getUserByToken($token) {
		$session = Sessions::find()->where(['=', 'token', $token])->one();
		if($session) {
			$user = User::findIdentity($session->uid);
			if($user) {
				return $user;
			}
			return false;
		} else {
			return false;
		}
	}	
	
	//Проверка авторизации
    public function actionCheckauth()
    {
		$token = $_POST['token'];
		if(self::getUserByToken($token)) {
			die('{"success":1}');
		}
		die('{"success":0,"errCode":1,"errDescr":"Вы не авторизованы"}');
    }
	
	//Авторизация
	public function actionAuth() {		
		$_POST = $_REQUEST;

		$phone = $_POST['phone'];
		
		$model = new SignupForm();		
		$model->phone = substr($phone, -10);
		
		if($phone == '') {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 14; 
			$return_data->errDescr = "Телефон введен неверно";
			print json_encode($return_data);
			die();
		}
		
		$user = User::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'phone_confirm', 1]])->one();
		if (!$user) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 14; 
			$return_data->errDescr = "Пользователя с такими данными не найдено, или у него не подтвержден телефон";
			print json_encode($return_data);
			die();
		} else {
			$mobileCode = MobileCode::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'confirmed', 0], ['=', 'type_code', 'auth']])->one();
			if(!$mobileCode) {
				$mobileCode = new MobileCode;
				$mobileCode->phone = $model->phone;
				$mobileCode->confirmed = 0;
				$mobileCode->type_code = 'auth';
			} else {
				if($mobileCode->time_try+120 > time()) {
					$return_data = new \stdClass();
					$return_data->success = 0;	
					$return_data->errCode = 13; 
					$return_data->errDescr = "Нельзя отправлять код так часто";
					print json_encode($return_data);
					die();
				}
			}
			
			$mobileCode->code = (string)rand(1000, 9999);
			$mobileCode->try = 0;
			$mobileCode->time_try = time();
			
			if(!$mobileCode->save()) {
				$return_data = new \stdClass();
				$return_data->success = 0;	
				$return_data->errCode = 13; 
				$return_data->errDescr = "Не удалось создать код проверки";
				print json_encode($return_data);
				die();
			}
			
			$textMessage = $mobileCode->code.'';
			
			//self::sms_service($model->phone, $textMessage);
						
			$return_data = new \stdClass();
			$return_data->success = 1;	
			return json_encode($return_data);
		} 
	}
		
	//Проверка кода после авторизации
	public function actionCheckauthcode() {			
		$_POST = $_REQUEST;
		
		$token = md5(time());
		$phone = $_POST['phone'];
		$code = $_POST['code'];
		
		$phone = substr($phone, -10);
		
		$mobileCode = MobileCode::find()->where(['AND', ['=', 'phone', $phone], ['=', 'confirmed', 0], ['=', 'type_code', 'auth']])->orderBy('id desc')->one();
		if(!$mobileCode) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 17; 
			$return_data->errDescr = "Не найдено пользователя с такими данными";
			print json_encode($return_data);
			die();
		} 
		
		if($mobileCode->confirmed == 1) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 18; 
			$return_data->errDescr = "Пользователь уже авторизован";
			print json_encode($return_data);
			die();
		}
		
		if($code != $mobileCode->code) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 19; 
			$return_data->errDescr = "Код активации введен неверно";
			print json_encode($return_data);
			die();
		} else {
			$user = User::find()->where(['LIKE', 'phone', $phone])->one();
			if($user) {
				
				$mobileCode->confirmed = 1;
				$mobileCode->save();
				
				$session = Sessions::find()->where(['and', ['=', 'uid', $user->id], ['=', 'token', $token]])->one();
				if(!$session) {
					$session = new Sessions;
					$session->uid = $user->id;
					$session->token = $token;
					$session->date_created = date('Y-m-d H:i:s');
					$session->save();			
				} else {
					$session->date_created = date('Y-m-d H:i:s');
					$session->save();			
				}
				
				$return_data = new \stdClass();
				$return_data->success = 1;	
				$return_data->token = $token;
				
				return json_encode($return_data);
			} else {
				$return_data = new \stdClass();
				$return_data->success = 0;	
				$return_data->errCode = 3; 
				$return_data->errDescr = "Пользователь с таким телефоном не найден";
				print json_encode($return_data);
				die();
			}			
		}
		
		return json_encode($return_data);
	}	
	
	//Регистрация
	public function actionRegister() {		
		$_POST = $_REQUEST;

		$phone = $_POST['phone'];
		
		$model = new SignupForm();		
		$model->phone = substr($phone, -10);
		
		if($phone == '') {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 14; 
			$return_data->errDescr = "Телефон введен неверно";
			print json_encode($return_data);
			die();
		}
		
		$user = User::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'phone_confirm', 1]])->one();
		if ($user) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 14; 
			$return_data->errDescr = "Пользователь с данным телефоном уже зарегистрирован";
			print json_encode($return_data);
			die();
		}
		
		$user = User::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'phone_confirm', 0]])->one();
		if ($user || $user = $model->signupMobile()) {
			$mobileCode = MobileCode::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'confirmed', 0], ['=', 'type_code', 'reg']])->one();
			if(!$mobileCode) {
				$mobileCode = new MobileCode;
				$mobileCode->phone = $model->phone;
				$mobileCode->confirmed = 0;
				$mobileCode->type_code = 'reg';
			} else {
				if($mobileCode->time_try+120 > time()) {
					$return_data = new \stdClass();
					$return_data->success = 0;	
					$return_data->errCode = 13; 
					$return_data->errDescr = "Нельзя отправлять код так часто";
					print json_encode($return_data);
					die();
				}
			}
			
			$mobileCode->code = (string)rand(1000, 9999);
			$mobileCode->try = 0;
			$mobileCode->time_try = time();
			
			if(!$mobileCode->save()) {
				$return_data = new \stdClass();
				$return_data->success = 0;	
				$return_data->errCode = 13; 
				$return_data->errDescr = "Не удалось создать код проверки";
				print json_encode($return_data);
				die();
			}
			
			$textMessage = "Код: ".$mobileCode->code.' подтверждения для завершения регистрации';
			
			//self::sms_service($model->phone, $textMessage);
						
			$return_data = new \stdClass();
			$return_data->success = 1;	
			return json_encode($return_data);
		} else {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 12; 
			$return_data->errDescr = "Не удалось зарегистрироваться";
			print json_encode($return_data);
			die();
		}
	}
		
	//Проверка кода после регистрации
	public function actionCheckregcode() {			
		$_POST = $_REQUEST;
		
		$token = md5(time());
		$phone = $_POST['phone'];
		$code = $_POST['code'];
		
		$phone = substr($phone, -10);
		
		$mobileCode = MobileCode::find()->where(['AND', ['=', 'phone', $phone], ['=', 'confirmed', 0], ['=', 'type_code', 'reg']])->orderBy('id desc')->one();
		if(!$mobileCode) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 17; 
			$return_data->errDescr = "Не найдено пользователя с такими данными";
			print json_encode($return_data);
			die();
		} 
		
		if($mobileCode->confirmed == 1) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 18; 
			$return_data->errDescr = "Пользователь уже подтвержден";
			print json_encode($return_data);
			die();
		}
		
		if($code != $mobileCode->code) {
			$return_data = new \stdClass();
			$return_data->success = 0;	
			$return_data->errCode = 19; 
			$return_data->errDescr = "Код активации введен неверно";
			print json_encode($return_data);
			die();
		} else {
			$user = User::find()->where(['LIKE', 'phone', $phone])->one();
			if($user) {
				$user->phone_confirm = 1;
				$user->save();
				
				$mobileCode->confirmed = 1;
				$mobileCode->save();
				
				$session = Sessions::find()->where(['and', ['=', 'uid', $user->id], ['=', 'token', $token]])->one();
				if(!$session) {
					$session = new Sessions;
					$session->uid = $user->id;
					$session->token = $token;
					$session->date_created = date('Y-m-d H:i:s');
					$session->save();			
				} else {
					$session->date_created = date('Y-m-d H:i:s');
					$session->save();			
				}
				
				$return_data = new \stdClass();
				$return_data->success = 1;	
				$return_data->token = $token;
				
				return json_encode($return_data);
			} else {
				$return_data = new \stdClass();
				$return_data->success = 0;	
				$return_data->errCode = 3; 
				$return_data->errDescr = "Пользователь с таким телефоном не найден";
				print json_encode($return_data);
				die();
			}			
		}
		
		return json_encode($return_data);
	}
				
	private function sms_service($phone, $message) {
		$key = 'fn4qUxlNfHcX82RayZYLiihuu684';
		
		$ch = curl_init("http://sms.ru/sms/send");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			"api_id"		=>	"34E752BA-728E-4C17-0C15-AE1D91BF95B7",
			"to"			=>	"7".$phone,
			"text"		=>	$message,
			"from"		=> 'heyMaster'
		));
		$body = curl_exec($ch);
		curl_close($ch);
		
		return $body;
	}
}
