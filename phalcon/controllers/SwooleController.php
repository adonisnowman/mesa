<?php

use Phalcon\Http\Response;

class SwooleController extends BaseController
{

      public static $PostData = [];
      public static $checkKeys = [];

      public function initialize()
      {
          date_default_timezone_set( 'asia/taipei' );
          session_name("swoole");
          session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST']);
  
          session_start();
  
  
          $response = new Response();
  
          $response->setContentType('application/json', 'UTF-8');
          $response->setHeader('Access-Control-Allow-Origin', '*');
          // if (!empty($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == DOMAIN_DEV )) 
          {
              $postdata = file_get_contents("php://input");
              $postdata = json_decode($postdata, true);
              if (is_array($postdata) && isset($postdata['Action'])) {
                  $_POST = $postdata;
              }
          }
  
          $response->setHeader('Access-Control-Allow-Methods', 'POST');
          $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Range, Content-Disposition, Content-Type, Authorization');
          $response->setHeader('Content-type', 'application/json');
          $response->sendHeaders();
      }


      public function indexAction()
      {
            if (!empty($_SESSION['Accounts']['UniqueID']))
                  _Api::AccountsLocation($_SESSION['Accounts']['UniqueID']);

            _Api::HTTP_HOST_Check();
            $Action = NULL;
            if (!empty($_POST)) extract($_POST, EXTR_OVERWRITE, "");

            //抓取本Controller的相關Actions設定
            $Item = [];
            $Item["ControllerName"] = ucfirst($this->router->getControllerName()) . "Controller";
            $Item["ActionName"]  = $Action;

            self::$checkKeys = _Action::_checkKeys($Item);

            //這裡只判斷 Action 存不存在

            if (empty($Action) || !in_array($Action, array_keys(self::$checkKeys))) {
                  $Return['ErrorMsg'][] = "Action not Find";
                  $Return['Action'] = $Action;
                  $Return['checkKeys'] = array_keys(self::$checkKeys);
                  echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
                  exit;
            }
            //判斷 checkKeys 存不存在 並產生 回傳的 self::$PostData 正確格式
            self::$PostData = _Api::checkPostData(self::$checkKeys[$Action]);

            
            //ActionLogs       
            $Item["ActionName"] = $Action;
            $Item["ActionPort"] = _Action::ActionPort($_SERVER['HTTP_HOST']);
            $checkKeys = checkKeys::getObjectByItem($Item);

            if (!empty($checkKeys) && $checkKeys->offshelf) {
                  $Return['ErrorMsg'][] = "Api Closed";
                  echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
                  exit;
            } else if (empty($checkKeys)) {

                  var_dump($Item);
                  exit;
            }
            //checkKeys
            _Action::checkKeys($checkKeys, $Item);
            $Insert = _Action::ActionLogs($this->router, self::$PostData, $checkKeys);

           
            //紀錄ActionLogs
            $Return = Models::insertTable($Insert, "ActionLogs");
            if (empty($Return['Object']->UniqueID)) return $Return;
            else $ActionLogs = $Return['Object'];

            
            $Return = $this->$Action();
            $Return['Action'] = $Action;
            

            if (!empty($checkKeys->UniqueID)) {
                  $checkKeys->called_time = Tools::getDateTime();
                  $checkKeys->save();
            }

            if (!empty($ActionLogs->UniqueID)) {
                  

                  $ActionLogs->Return_JSON = JSON_encode($Return, JSON_UNESCAPED_UNICODE);
                  $ActionLogs->finish_time = Tools::getDateTime();
                  $ActionLogs->save();
            }

            if (!empty($Return['UniqueID']) && _UniqueID::checkUniqueID($Return['UniqueID'])) {
                  $Return['UniqueID'] = _UniqueID::publicUniqueID($Return['UniqueID']);
            }

            //系統導頁面判斷
            if (!empty($Return['ReDirect'])) {
                  $Item = [];
                  $History = $Return['ReDirect'];
                  $Item['ReDirect'] = $Return['ReDirect'];
                  if (Tools::getIp() == Tools::ServerIp()) {
                        if (!empty($_SESSION['Admin']['ReDirect'])) $Return['ReDirect'] = "reload";
                        else $Return['ReDirect'] = $History;
                  } else {
                        if (!empty($_SESSION['User']['ReDirect'])) $Return['ReDirect'] = "reload";
                        else $Return['ReDirect'] = $History;
                  }
            }
            echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
      }

      public function SocketList(){
            $UniqueID = self::$PostData['UniqueID'];
            
            
            if($_SERVER['SERVER_ADDR'] == "10.0.1.2") $SocketLost[] = [ 'wss' => "adonis.tw" , "port" => "9509"];
            else $SocketLost[] = [ 'wss' => "swoole.bestaup.com" , "port" => "8080"];


            $Return[__FUNCTION__] = $SocketLost;

            return $Return;
      }

      public function MessageToken(){

            $Return = [];
           
            if(empty(self::$PostData['shortUniqueID'])) {
                  $Return['ErrorMsg'][] = "請先登入";
                  return $Return;
            }

            $Item = [];
            $Item['shortUniqueID'] = self::$PostData['shortUniqueID'];

            $SwooleConnections = SwooleConnections::getObjectByItem($Item," logout_time IS NULL ");

         

            if(empty($SwooleConnections)) {
                  $Return['ErrorMsg'] = "請先登入" ;
                  return $Return;
            }

            if(empty($SwooleConnections->UsersLoginLogs)) {
                  $Return['ErrorMsg'] = "請先登入" ;
                  return $Return;
            }

            $UsersLoginLogs = $SwooleConnections->UsersLoginLogs;

            if( empty($UsersLoginLogs) || !empty($UsersLoginLogs->logout_time)) {

                  $Return['ErrorMsg'] = "請先登入" ;
                  return $Return;

            } else {
                  $Item = [];
                  $Item['UniqueID_UsersLoginLogs'] = $UsersLoginLogs->UniqueID;                  
                  $Item['shortUniqueID_SwooleConnections'] = $SwooleConnections->shortUniqueID;
            }

            $MessageType = MessageType::find();


            foreach(  $MessageType AS $row ) {

                  if(empty( $row->UserMessageTypeDefaultCosts )) {



                        $Item['UniqueID_MessageType'] = $row->UniqueID;

                       
                        $MessageToken = MessageToken::getObjectByItem($Item," ( faild_time IS NULL AND  used_time IS NULL  ) ");

                        if(!empty($MessageToken)) {
                              $MessageToken->faild_time = Tools::getDateTime();
                              $MessageToken->save();
                        } 



                        //每次呼叫，都必定產生新的token
                      
                        $MessageToken = models::insertTable($Item,"MessageToken",true);
                        
                        
                        if(!empty($MessageToken->UniqueID)) $Return[__FUNCTION__][$row->name] = $MessageToken->action_token ;


                  }
            }



            return $Return;
            
      }


      public function Connect(){

            $UniqueID = self::$PostData['query_string'];
            $user_md5 = self::$PostData['user_md5'];

            $UsersLoginLogs =(object) _UniqueID::LoadUniqueIDObject($UniqueID,"UsersLoginLogs")['Object'];
            if(!empty($UsersLoginLogs) ) {

                  if(empty($UsersLoginLogs->logout_time) && $user_md5 != md5( $UsersLoginLogs->HTTP_USER_AGENT . $UsersLoginLogs->REMOTE_ADDR )) {
                        //登入資料有誤
                        $UsersApiController = new UsersApiController();
                        $UsersApiController->logout();
                  }
                  
                  //連線紀錄

                  $Insert = Tools::fix_element_Key(self::$PostData,["shortUniqueID","user_md5","HTTP_HOST","REMOTE_ADDR","HTTP_USER_AGENT"]);
                  $Insert['UniqueID_UsersLoginLogs'] = $UsersLoginLogs->UniqueID;
                  $SwooleConnections = Models::insertTable($Insert, "SwooleConnections",true);

                  $Return[__FUNCTION__] = $SwooleConnections->toArray();

            }else{
                  $Return['ErrorMsg'][] = "請先登入";
            }

            return $Return;
            
      }

      public function DisConnect(){
           
            var_dump(self::$PostData);
            if(empty(self::$PostData['UniqueID'])) {
                  $Action = [];
                  $Action['Type'] = "Action";
                  $Action['Action'] = " sharedData.Logout(); ";
                  
                  $Return['Action'] = $Action;

                  return $Return;
            }

            $UniqueID = self::$PostData['UniqueID'];

            $SwooleConnections =(object) _UniqueID::LoadUniqueIDObject($UniqueID,"SwooleConnections")['Object'];

            $SwooleConnections->logout_time = Tools::getDateTime();
            $SwooleConnections->save();
      }
      public function SignTokenAction($UniqueID = "", $Token = "")
      {

            Tools::checkToken($Token);

            $Item['UniqueID'] = $UniqueID;
            $User = Users::getObjectById($Item);

            if (!empty($User)) {
                  $User->SignToken_time = Tools::getDateTime();
                  $User->save();

                  $Return = _Views::Init();
                  $Return['ReDirect'] = "signMobile";
                  $Return['UniqueID'] = $UniqueID;

                  echo _Views::RedirectAdmin($Return);
            }
      }
}
