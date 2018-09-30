<?php

namespace app\controllers;
error_reporting(E_ALL & ~E_NOTICE);

use app\models\AbonementsClients;
use app\models\AddressCab;
use app\models\Cabinet;
use app\models\Children;
use app\models\LogCustomerRecord;
use app\models\LogServicesPackage;
use app\models\MastersGraphs;
use app\models\Payer;
use app\models\PayerConnection;
use app\models\Record;
use app\models\ServicesPackage;
use Yii;
use app\models\Sessions;
use app\models\LoginForm;
use app\models\User;
use app\models\SignupForm;
use app\models\MobileCode;

class ApiexchangeController extends \yii\web\Controller
{
	

    /**
     * Проверка авторизации для проверки методов
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
	{		
		$_POST = $_REQUEST;
		
		setlocale (LC_TIME, "ru_RU");
		
		return parent::beforeAction($action);
	}

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        print($result);
        die();
    }

    /**
     * Получить пользователя по токену
     * @param $token
     * @return bool|object
     */
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

    /**
     * Возвращаем ошибку для мобильных систем
     * @param $code
     * @param $text
     * @return string
     */
	private function responceErrorJson($code, $text) {
        $return_data = new \stdClass();
        $return_data->success = 0;
        $return_data->errCode = $code;
        $return_data->errDescr = $text;
        print json_encode($return_data);
        die();
    }

    private function responceSuccessJson($name = '', $obj = null) {
        $return_data = new \stdClass();
        $return_data->success = 1;
        if($name != null) {
            $return_data->$name = $obj;
        }
        print json_encode($return_data);
        die();
    }

    /**
     * Проверка авторизации пользователя. Токен берется из post
     */
    public function actionCheckauth()
    {
		$token = $_POST['token'];
		if(self::getUserByToken($token)) {
			die('{"success":1}');
		}
        $this->responceErrorJson(1, "Вы не авторизованы");
    }
	
	//Авторизация
	public function actionAuth() {		
		$phone = $_POST['phone'];
		
		$model = new SignupForm();		
		$model->phone = substr($phone, -10);
		
		if($phone == '') {
		    $this->responceErrorJson(1, "Телефон введен неверно");
		}
		
		$user = User::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'phone_confirm', 1]])->one();
		if (!$user) {
            $this->responceErrorJson(2, "Пользователя с такими данными не найдено, или у него не подтвержден телефон");
		} else {
			$mobileCode = MobileCode::find()->where(['AND', ['=', 'phone', $model->phone], ['=', 'confirmed', 0], ['=', 'type_code', 'auth']])->one();
			if(!$mobileCode) {
				$mobileCode = new MobileCode;
				$mobileCode->phone = $model->phone;
				$mobileCode->confirmed = 0;
				$mobileCode->type_code = 'auth';
			} else {
				if($mobileCode->time_try+120 > time()) {
                    $this->responceErrorJson(3, "Нельзя отправлять код так часто");
				}
			}
			
			$mobileCode->code = (string)rand(1000, 9999);
			$mobileCode->try = 0;
			$mobileCode->time_try = time();
			
			if(!$mobileCode->save()) {
                $this->responceErrorJson(4, "Не удалось создать код проверки");
			}
			
			$textMessage = $mobileCode->code.'';
			
			self::sms_service($model->phone, $textMessage);
						
			$return_data = new \stdClass();
			$return_data->success = 1;	
			return json_encode($return_data);
		} 
	}
		
	//Проверка кода после авторизации
	public function actionCheckauthcode() {
		$phone = $_POST['phone'];
		$code = $_POST['code'];

        $token = md5(time());
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
	}	
	
	//Регистрация
	public function actionRegister() {		
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
			
			self::sms_service($model->phone, $textMessage);
						
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

                $payer = new Payer();
                $payer->phone = $user->phone;
                $payer->b_date = null;
                $payer->job = '-';
                $payer->save();
				
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
	}


    /**
     * Получить мои данные, имя, фамилия
     */
    public function actionGetuserdata() {
        $token = $_POST['token'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $payer = Payer::findByPhone($user->phone);
        if(!$payer) {
            $payer = new Payer();
        }

        $userData = new \stdClass();
        $userData->phone = $user->phone;
        $userData->name = $payer->name;
        $userData->bdate = $payer->b_date == "0000-00-00" ? '' : $payer->b_date;
        $userData->job = $payer->job;

        $this->responceSuccessJson('user', $userData);
    }


    /**
     * Сохранить мои данные, имя, фамилия
     */
    public function actionSaveuserdata() {
        $token = $_POST['token'];
        $name = $_POST['name'];
        $bdate = $_POST['bdate'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $payer = Payer::findByPhone($user->phone);
        if(!$payer) {
            $payer = new Payer();
        }

        $payer->name = $name;
        $payer->phone = $user->phone;
        $payer->b_date = $bdate ? $bdate : null;
        $payer->job = '-';

        if(!$payer->save()) {
            $this->responceErrorJson(2, "Данные переданы неверно");
        }
        $this->responceSuccessJson();
    }


    /**
     * Получить json список детей
     */
    public function actionGetchilds() {
        $token = $_POST['token'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $payer = new Payer();
            $payer->phone = $user->phone;
            $payer->b_date = null;
            $payer->job = '-';
            $payer->save();
        }

        $childs = array();
        if(count($payer->childs)) {
            foreach ($payer->childs as $childObj) {
                $child = new \stdClass();
                $child->id = $childObj->id;
                $child->firstName = $childObj->name;
                $child->birthDate = $childObj->b_date;
                $child->balance = $childObj->client_balance;

                array_push($childs, $child);
            }
        }

        $this->responceSuccessJson('childs', $childs);
    }

    /**
     * Сохранить ребенка. Если не передан id то создаем нового
     */
    public function actionSavechild() {
        $token = $_POST['token'];
        $id = $_POST['id'];
        $name = $_POST['name'];
        $bdate = $_POST['bdate'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }
        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $payer = new Payer();
            $payer->phone = $user->phone;
            $payer->b_date = null;
            $payer->job = '-';
            $payer->save();
        }

        if(!$id) {
            $child = new Children();
        } else {
            $check = false;

            if(count($payer->childs)) {
                foreach ($payer->childs as $childBaseObj) {
                    if($childBaseObj->id == $id) {
                        $check = true;
                    }
                }
            }

            if(!$check) {
                $this->responceErrorJson(4, "Неверно передан параметр ребенка");
            }

            $child = Children::findOne(['id' => $id]);
        }

        $child->name = $name;
        $child->b_date = $bdate ? date('Y-m-d', strtotime($bdate)) : '0000-00-00';
        $child->phone = $payer->phone;
        $child->mail = '-';

        if(!$child->save()) {
            $this->responceErrorJson(2, "Данные переданы неверно");
        }

        $payerChild = new PayerConnection();
        $payerChild->payer_id = $payer->id;
        $payerChild->user_id = $child->id;
        if(!$payerChild->save()) {
            $this->responceErrorJson(3, "Не удалось сохранить связь ребенка и плательщика");
        }

        $this->responceSuccessJson();
    }


    /**
     * Удалить ребенка
     */
    public function actionDeletechild() {
        $token = $_POST['token'];
        $id = $_POST['id'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }
        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        if(!$id) {
            $child = new Children();
        } else {
            $check = false;

            if(count($payer->childs)) {
                foreach ($payer->childs as $childBaseObj) {
                    if($childBaseObj->id == $id) {
                        $check = true;
                    }
                }
            }

            if(!$check) {
                $this->responceErrorJson(4, "Неверно передан параметр ребенка");
            }

            $child = Children::findOne(['id' => $id]);
        }

        $child->delete_user = 1;
        if(!$child->save()) {
            $this->responceErrorJson(2, "Данные переданы неверно");
        }

        $payerChild = PayerConnection::findByPayerId($payer->id, $id);
        if(!$payerChild->delete()) {
            $this->responceErrorJson(3, "Не удалось удалить связь ребенка и плательщика");
        }

        $this->responceSuccessJson();
    }



    /**
     * Получить список записей
     */
    public function actionGetrecords() {
        $token = $_POST['token'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }
        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $ids = array();
        if(count($payer->childs)) {
            foreach ($payer->childs as $childBaseObj) {
                array_push($ids, $childBaseObj->id);
            }
        }

        if(!count($ids)) {
            $this->responceSuccessJson('records', []);
        }

        $records = Record::find()->where(['AND', ['client_id'=>$ids]])->orderBy("date_record DESC")->all();

        $recordPast = array();
        $recordNext = array();
        if(count($records)) {
            foreach($records as $recordObj) {
                $localRecord = new \stdClass();

                $localRecord->id = $recordObj->id;
                $localRecord->datetime = $this->rdate('d M', strtotime($recordObj->date_record)).', '.$this->output_time($recordObj->time_record);

                $localRecord->time = $recordObj->time_record;

                $localRecord->performed = $recordObj->performed ? $recordObj->performed : 0;
                $localRecord->date = $recordObj->date_record;

                $recordMaster = Children::findOne(['id' => $recordObj->master_id]);
                $recordChild = Children::findOne(['id' => $recordObj->client_id]);
                $recordCab = Cabinet::findOne(['id' => $recordObj->cabinet_id]);

                $localRecord->trainer_name = $recordMaster->name;
                $localRecord->child_name = $recordChild->name;
                $localRecord->cabinet_name = $recordCab->name;

                if(strtotime($recordObj->date_record) < time()) {
                    array_push($recordPast, $localRecord);
                } else {
                    array_push($recordNext, $localRecord);
                }
            }
        }

        $recordPastNext = new \stdClass();
        $recordPastNext->past = $recordPast;
        $recordPastNext->next = $recordNext;

        $this->responceSuccessJson('records', $recordPastNext);
    }


    public function actionGetcabinets() {
        $token = $_POST['token'];
        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $cabinets = AddressCab::find()->all();
        $cabinetsArr = array();

        $anyCab = new \stdClass();
        $anyCab->id = -1;
        $anyCab->name = 'Любой';

        array_push($cabinetsArr, $anyCab);

        foreach($cabinets as $cabinetObj) {
            $cab = new \stdClass();
            $cab->id = $cabinetObj->id;
            $cab->name = $cabinetObj->name;

            array_push($cabinetsArr, $cab);
        }

        $this->responceSuccessJson('address', $cabinetsArr);
    }

    public function actionGetmasters() {
        $token = $_POST['token'];
        $address = $_POST['address'];
        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        if(!$address) {
            $address = -1;
        }

        $cabinetsArr = array();
        if($address != -1) {
            $addressObj = AddressCab::findOne(['id' => $address]);
            foreach($addressObj->cabinets as $cabinet) {
                array_push($cabinetsArr, $cabinet->id);
            }
        } else {
            $cabinets = Cabinet::find()->all();
            foreach($cabinets as $cabinet) {
                array_push($cabinetsArr, $cabinet->id);
            }
        }

        $graphs = MastersGraphs::find()->where(['AND', ['in', 'cab_id', $cabinetsArr], ['>', 'date', date('Y-m-d')], ['<', 'date', date('Y-m-d', time() + 86400 * 30)]])->all();

        $masterIds = array();
        foreach ($graphs as $graph) {
            array_push($masterIds, $graph->master_id);
        }

        $masterIds = array_unique($masterIds);
        $masterArr = array();
        foreach ($masterIds as $masterId) {
            $master = Children::findOne(['id'=>$masterId]);
            $masterObj = new \stdClass();
            $masterObj->id = $master->id;
            $masterObj->name = $master->name;

            if(isset($master->img)) {
                $masterObj->img = $master->img;
            } else {
                $masterObj->img = 'https://yt3.ggpht.com/a-/AN66SAy8cvtgmsdE9EnCfkZpGQKedZS-ePChhyed8Q=s48-mo-c-c0xffffffff-rj-k-no';
            }

            if(isset($master->description)) {
                $masterObj->description = $master->description;
            }

            array_push($masterArr, $masterObj);
        }

        $this->responceSuccessJson('masters', $masterArr);
    }


    public function actionGettimes() {
        $token = $_POST['token'];
        $address = $_POST['address'];
        $master = $_POST['master'];
        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $cabinetsArr = array();
        if($address != -1) {
            $addressObj = AddressCab::findOne(['id' => $address]);
            if (count($addressObj->cabinets)) {
                foreach ($addressObj->cabinets as $cabinet) {
                    array_push($cabinetsArr, $cabinet->id);
                }
            }
        } else {
            $cabinets = Cabinet::find()->all();
            foreach($cabinets as $cabinet) {
                array_push($cabinetsArr, $cabinet->id);
            }
        }

        if($master != -1) {
            $graphs = MastersGraphs::find()->where(['AND', ['master_id'=>$master], ['in', 'cab_id', $cabinetsArr], ['>', 'date', date('Y-m-d')], ['<', 'date', date('Y-m-d', time() + 86400 * 30)], ['type'=>1]])->all();
        } else {
            $graphs = MastersGraphs::find()->where(['AND', ['in', 'cab_id', $cabinetsArr], ['>', 'date', date('Y-m-d')], ['<', 'date', date('Y-m-d', time() + 86400 * 30)], ['type'=>1]])->all();
        }

        $workDates = array();

        foreach($graphs as $graph) {
            $workDay = new \stdClass();
            $workDay->master_id = $graph->master_id;
            $workDay->time_work = $this->getWorkTimeArr($graph->time_work);

            $workDates[$graph->date][$graph->cab_id][] = $workDay;
        }

        ksort($workDates);

        $records = Record::find()->where(['AND', ['in', 'cabinet_id', $cabinetsArr], ['>', 'date_record', date('Y-m-d')]])->all();
        $recordsDates = array();
        foreach ($records as $record) {
            $recordsDay = new \stdClass();
            $recordsDay->start = $record->time_record;
            $recordsDay->finish = $record->time_record + $record->duration_for_count;

            $recordsDates[$record->date_record][$record->cabinet_id][] = $recordsDay;
        }

        $cabsArr = array();
        $cabs = Cabinet::find()->where(['in', 'id', $cabinetsArr])->all();
        foreach ($cabs as $cab) {
            $cabObj = new \stdClass();
            $cabObj->name = $cab->name;
            $cabObj->shirt_name = $cab->shirt_name;

            $cabsArr[$cab->id] = $cabObj;
        }

        $times = array();

        foreach($workDates as $date => $workDay) {
            foreach ($workDay as $cabinet_id => $workDayCab) {
                foreach($workDayCab as $workDayMaster) {
                    $master_id = $workDayMaster->master_id;
                    foreach($workDayMaster->time_work as $workTime) {
                        //Формирование строки для вывода на страницу
                        $output_text = $this->output_time($workTime);
                        $output_text .= '–';
                        $output_text .= $this->output_time($workTime+0.5);

                        $check = false;

                        if(count($recordsDates[$date][$cabinet_id])) {
                            foreach ($recordsDates[$date][$cabinet_id] as $record) {
                                if (!(
                                    $workTime < $record->start && $workTime + 0.5 <= $record->start ||
                                    $workTime >= $record->finish
                                )
                                ) {
                                    $check = true;
                                }
                            }
                        }

                        if(!$check) {
                            $times[$date][] = array('cab_id'=>$cabinet_id, 'master_id'=>$master_id, 'start'=>$workTime, 'finish'=>$workTime+0.5, 'output_text'=>$output_text, 'cab_name' => $cabsArr[$cabinet_id]->name, 'mobileCabName' => $cabsArr[$cabinet_id]->shirt_name);
                        }
                    }
                }
            }
        }

        $finalArr = array();

        if(count($times)) {
            foreach($times as $date => $workDay) {
                $timesRecord = array();
                foreach($workDay as $accessTime) {
                    if(!in_array($accessTime['start'], $timesRecord)) {
                        $finalArr[$date][] = $accessTime;
                        $timesRecord[] = $accessTime['start'];
                    }
                }
                usort($finalArr[$date], function($a, $b) {
                    if($a['start'] > $b['start']) {
                        return true;
                    } else return false;
                });
            }
        }

        $this->responceSuccessJson('times', $finalArr);
    }

    public function actionCreaterecords()
    {
        $token = $_POST['token'];
        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $cab_id = $_POST['cab_id'];
        $master = $_POST['master'];
        $date = $_POST['date'];
        $start = $_POST['time'];
        $children = $_POST['children'];

        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $check = false;

        if(count($payer->childs)) {
            foreach ($payer->childs as $childBaseObj) {
                if($childBaseObj->id == $children) {
                    $check = true;
                }
            }
        }

        if(!$check) {
            $this->responceErrorJson(2, "Передан неверный параметр ребенка");
        }

        $graph = MastersGraphs::find()->where(['AND', ['date'=>$date], ['master_id'=>$master], ['cab_id'=>$cab_id], ['date'=>date('Y-m-d', strtotime($date))], ['type'=>1]])->one();
        if(!$graph) {
            $this->responceErrorJson(3, "Мастер не работает в этот день");
        }

        $masterWorkArr = $this->getWorkTimeArr($graph->time_work);

        if(!in_array($start, $masterWorkArr)) {
            $this->responceErrorJson(3, "Мастер не работает в этот день");
        }

        $records = Record::find()->where(['AND', ['cabinet_id' => $cab_id], ['time_record'=>$start], ['date_record'=>date('Y-m-d', strtotime($date))]])->all();
        if(count($records)) {
            $this->responceErrorJson(4, "Запись на это время уже невозможна");
        }

        $abonement1 = AbonementsClients::find()->where(['AND', ['<=', 'date_to', $date], ['>=', 'date_from', $date], ['user_id'=>$children]])->orderBy('date_from')->one();
        $abonement2 = AbonementsClients::find()->where(['AND', ['user_id'=>$children]])->one();

        if(!$abonement1 && $abonement2) {
            $this->responceErrorJson(5, "У вас закончился абонемент. Запись не удалась.");
        }

        if(!$abonement1){
            $this->responceErrorJson(6, "У вас отсутствует абонемент. Запись не удалась.");
        }

        $paymentType = 0;

        $newRecord = new Record();
        $newRecord->cabinet_id = $cab_id;
        $newRecord->master_id = $master;
        $newRecord->client_id = $children;
        $newRecord->cert_number = 0;
        $newRecord->duration = '30м';
        $newRecord->duration_for_count = 0.5;
        $newRecord->date_record = $date;
        $newRecord->time_record = $start;
        $newRecord->pedicur_from = 0;
        $newRecord->pedicur_to = 0;
        $newRecord->abonement_id = $abonement1->id;

        if(!$newRecord->save()) {
            print_r($newRecord->getErrors());
            $this->responceErrorJson(7, "Не удалось сохранить запись.");
        } else {
            $servicePackage = new ServicesPackage();
            $servicePackage->customer_record_id = $newRecord->id;
            $servicePackage->service_id = 48;
            $servicePackage->count = 1;
            $servicePackage->save();
        }

        $this->responceSuccessJson();
    }

    public function actionRemoverecord() {
        $token = $_POST['token'];
        $id = $_POST['id'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $childIds = array();
        if(count($payer->childs)) {
            foreach ($payer->childs as $childBaseObj) {
                array_push($childIds, $childBaseObj->id);
            }
        }

        if(!count($childIds)) {
            $this->responceErrorJson(2, "Запись не найдена");
        }

        $record = Record::find()->where(['AND', ['client_id'=>$childIds], ['id'=>$id]])->orderBy("date_record DESC")->one();

        if(!$record) {
            $this->responceErrorJson(2, "Запись не найдена");
        }

        $record->delete();

        $this->responceSuccessJson();
    }


    public function actionConfirmrecord() {
        $token = $_POST['token'];
        $id = $_POST['id'];

        if(!($user = $this->getUserByToken($token))) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $payer = Payer::findByPhone($user->phone);
        if(!($payer)) {
            $this->responceErrorJson(1, "Вы не авторизованы");
        }

        $childIds = array();
        if(count($payer->childs)) {
            foreach ($payer->childs as $childBaseObj) {
                array_push($childIds, $childBaseObj->id);
            }
        }

        if(!count($childIds)) {
            $this->responceErrorJson(2, "Запись не найдена");
        }

        $record = Record::find()->where(['AND', ['client_id'=>$childIds], ['id'=>$id]])->orderBy("date_record DESC")->one();

        if(!$record) {
            $this->responceErrorJson(2, "Запись не найдена");
        }

        $record->confirmed = 1;
        $record->save();

        $this->responceSuccessJson();
    }







    private function getWorkTimeArr($str) {
        $str = trim($str, "¿");
        return explode('¿', $str);
    }

    //Вывод русского месяца
    private function rdate($param, $time=0) {
        if(intval($time)==0)$time=time();
        $MonthNames=array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
        if(strpos($param,'M')===false) return date($param, $time);
        else return date(str_replace('M',$MonthNames[date('n',$time)-1],$param), $time);
    }

    //Формирование строки для времени из формата 9.5 в формат 9:30
    private function output_time($time) {
        $time = explode('.', $time);
        if(!$time[1] || $time[1] == '0') {
            $time = $time[0].':00';
        } else {
            $time = $time[0].':30';
        }
        return $time;
    }

    private function sms_service($phone, $message) {
		$ch = curl_init("http://sms.ru/sms/send");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "api_id"		=>	"6AF1E99B-078C-14A6-BE54-4C2E48E214EF",
            "to"			=>	"7".$phone,
            "text"		=>	$message,
            "from"		=> 'KABINET'
		));
		$body = curl_exec($ch);
		curl_close($ch);
		
		return $body;
	}

    //Функция для сортировки двумерного массива пустых времен
    function sort_time($a, $b) {
        if($a->start > $b->start) {
            return 1;
        } else {
            return -1;
        }
    }
}
