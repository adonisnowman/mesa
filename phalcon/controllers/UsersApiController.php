<?php

use Phalcon\Http\Response;

class UsersApiController extends BaseController
{


    public static $arrContextOptions = [];
    public static $PostData = [];
    public static $checkKeys = [];
    public static $Pages = [];
    public static $ActionLogs = null;

    public function initialize()
    {
        date_default_timezone_set('asia/taipei');
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

        if (!empty($_SESSION[Tools::getIp()]['UniqueID']))
            _Api::UsersLocation($_SESSION[Tools::getIp()]['UniqueID']);

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
        $Item["ActionPort"] = _Action::ActionPort($_SERVER['HTTP_ORIGIN'], $Item);

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

        //頁次紀錄
        if (!empty(self::$PostData['Pages'])) self::$Pages = self::$PostData['Pages'];
        self::$Pages['List'] = [];

        //紀錄ActionLogs
        $Return = Models::insertTable($Insert, "ActionLogs");
        if (empty($Return['Object']->UniqueID)) return $Return;
        else self::$ActionLogs = $Return['Object'];

        _UsersApi::insertUserFootPoint(__CLASS__, $Action);
        $Return = $this->$Action();
        $Return['Action'] = $Action;
        $Return['Pages'] = self::$Pages;

        if (!empty($checkKeys->UniqueID)) {
            $checkKeys->called_time = Tools::getDateTime();
            $checkKeys->save();
        }

        if (!empty($ActionLogs->UniqueID)) {
            //這邊不在讀取資料表判斷
            if (!empty($_SESSION[Tools::getIp()]['Users'])) {
                self::$ActionLogs->UniqueID_UsersLoginLogs = $_SESSION[Tools::getIp()]['Users']['UniqueID_UsersLoginLogs'];
                self::$ActionLogs->UniqueID_Users = $_SESSION[Tools::getIp()]['Users']['UniqueID'];
            } else if (!empty(self::$PostData['UniqueID'])) {
                self::$ActionLogs->UniqueID_UsersLoginLogs = self::$PostData['UniqueID'];
            }

            self::$ActionLogs->Return_JSON = JSON_encode($Return, JSON_UNESCAPED_UNICODE);
            self::$ActionLogs->finish_time = Tools::getDateTime();
            self::$ActionLogs->save();
        }

        if (!empty($Return['UniqueID']) && _UniqueID::checkUniqueID($Return['UniqueID'])) {
            $Return['UniqueID'] = _UniqueID::publicUniqueID($Return['UniqueID']);
        }

        //系統導頁面判斷
        if (!empty($Return['ReDirect'])) {
            $Item = [];
            $History = $Return['ReDirect'];
            $Item['ReDirect'] = $Return['ReDirect'];
            //$_SESSION[Tools::getIp()]['ReDirect'] = RedirectAdmin::getOneByItem($Item);
            if (!empty($_SESSION[Tools::getIp()]['ReDirect'])) $Return['ReDirect'] = "reload";
            else $Return['ReDirect'] = $History;
        }
        echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
    }

    //導頁
    public function ReDirect()
    {
        $Return = [];
        $Item = [];
        $Item['ReDirect'] = self::$PostData['ReDirect'];

        $_SESSION[Tools::getIp()]['ReDirect'] = RedirectAdmin::getOneByItem($Item);
        $_SESSION['Action'] = "Redirect";
        if (!empty($_SESSION[Tools::getIp()]['ReDirect']))
            $Return['ReDirect'] = $_SESSION[Tools::getIp()]['ReDirect'];
        return $Return;
    }
    public function SignInSession()
    {

        $Return['SignInSession'] = [];
        if (!empty($_SESSION[Tools::getIp()]['SignInSession'])) {

            $Return['SignInSession'] = $_SESSION[Tools::getIp()]['SignInSession'];
            $Return['SignInSession']['user_md5'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        }


        return $Return;
    }
    //註冊
    public function Create()
    {
        $shortUniqueID = false;
        if (abs(round(((float)microtime(true))) == $_COOKIE['CreateTime'])) {

            $shortUniqueID = _UniqueID::shortUniqueID();
            $_COOKIE['CreateTime'] = $shortUniqueID;
        }

        $Insert = Tools::fix_element_Key(self::$PostData, ["account", "mobile", "password"]);

        //會員帳號判斷
        $SignInList = SignInList::getObjectByItem(["account" => $Insert['account']]);
        $UsedEmail = UsersConnection::getListObjectByItem(["email" => $Insert['account']]);
        $DisableEmail = UsersConnection::getListObjectByItem(["email_disable" => $Insert['account']]);


        if (!empty($SignInList->account)) {

            $Return['ErrorMsg'][] = "會員Email已註冊過了";
            return $Return;
        }
        if (count($UsedEmail)) {

            $Return['ErrorMsg'][] = "會員Email已被登記使用";
            return $Return;
        }
        if (count($DisableEmail)) {

            $Return['ErrorMsg'][] = "會員Email已被停用了";
            return $Return;
        } else {
            //新增註冊資料
            $_SESSION[Tools::getIp()]['SignInSession'] = [];
            $SignInList = Models::insertTable($Insert, "SignInList", true);

            if (empty($SignInList->UniqueID)) return $SignInList;   //註冊資料新增失敗

            //新增 身分驗證

            $Insert = [];
            $Insert['UniqueID_SignInList'] = $SignInList->UniqueID;

            $SignInCkecked = Models::insertTable($Insert, "SignInCkecked", true);

            if (empty($SignInCkecked->UniqueID)) return $SignInCkecked; //身份驗證新增失敗

            $_SESSION[Tools::getIp()]['SignInSession']['CreateTime'] = $shortUniqueID;
            $_SESSION[Tools::getIp()]['SignInSession']['account'] = $SignInList->account;
            $_SESSION[Tools::getIp()]['SignInSession']['mobile'] = $SignInList->mobile;






            //回傳資料

            $_SESSION[Tools::getIp()]['History'] = "UserSign";
            $_SESSION[Tools::getIp()]['ReDirect'] = "UserLogin";

            $Return['ReDirect'] = "UserLogin";
            return $Return;
        }
    }

    //電子郵件認證發送
    public function EmailVerify()
    {

        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {

            $Return['ErrorMsg'] = "請先登入";
            return $Return;
        }


        $SignInList = $_SESSION[Tools::getIp()]['SignInList'];



        //讀取身分驗證資料表
        $Item['UniqueID_SignInList'] =  $SignInList['UniqueID'];
        $SignInCkecked = SignInCkecked::getObjectByItem($Item);

        //電子信箱認證資料表
        $Insert = Tools::fix_element_Key(self::$PostData, ["email"]);
        $Insert['UniqueID_SignInCkecked'] =  $SignInCkecked->UniqueID;
        $EmailChecked = EmailChecked::getListObjectByItem($Insert, " invalid_time IS NULL ");
        if (count($EmailChecked) > 0) $EmailChecked->update(["invalid_time" => Tools::getDateTime()]);

        $EmailChecked = Models::insertTable($Insert, "EmailChecked", true);
        $EmailChecked->email_token = Tools::getToken();
        $EmailChecked->save();

        $_Views = _Views::Init();
        $_Views['ReDirect'] = "signEmail";
        $_Views['UniqueID'] = $EmailChecked->UniqueID;
        $_Views['Token'] = Tools::getToken();

        Tools::emailSend($Insert['email'], "signEmail", _Views::RedirectAdmin($_Views));

        $Return['EmailChecked'] = $EmailChecked->toArray();
        $Return['ErrorMsg'][] = "請收您的驗證信件，點擊完成註冊";
        return $Return;
    }

    //電子郵件認證確認
    public function EmailChecked()
    {

        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {

            $Return['ErrorMsg'] = "請先登入";
            return $Return;
        }

        if (!empty(self::$PostData["email_token"])) Tools::checkToken(self::$PostData["email_token"]);

        $SignInList = $_SESSION[Tools::getIp()]['SignInList'];

        //讀取身分驗證資料表
        $Item['UniqueID_SignInList'] =  $SignInList['UniqueID'];
        $SignInCkecked = SignInCkecked::getObjectByItem($Item);

        //電子信箱認證資料表
        $Item = Tools::fix_element_Key(self::$PostData, ["UniqueID", "email", "email_token"]);
        $Item['UniqueID_SignInCkecked'] =  $SignInCkecked->UniqueID;

        $EmailChecked = EmailChecked::getObjectByItem($Item);

        if (empty($EmailChecked->UniqueID)) {

            $Return['ErrorMsg'][] = "查無相關Email 驗證資料";
            return $Return;
        } else {

            $EmailChecked->HTTP_HOST = $_SERVER['HTTP_HOST'];
            $EmailChecked->REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
            $EmailChecked->HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
            $EmailChecked->checked_time = Tools::getDateTime();
            $EmailChecked->save();

            $SignInCkecked->UniqueID_EmailChecked = $EmailChecked->UniqueID;
            $SignInCkecked->email_checked_time = Tools::getDateTime();
            $SignInCkecked->save();

            
        }

        $Return['ErrorMsg'][] = "完成Email驗證";
        return $Return;
    }

    //手機號碼認證發送
    public function MobileVerify()
    {

        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {

            $Return['ErrorMsg'] = "請先登入";
            return $Return;
        }


        $SignInList = $_SESSION[Tools::getIp()]['SignInList'];

        //讀取身分驗證資料表
        $Item['UniqueID_SignInList'] =  $SignInList['UniqueID'];
        $SignInCkecked = SignInCkecked::getObjectByItem($Item);

        //讀取手機號碼認證資料表
        $Insert = Tools::fix_element_Key(self::$PostData, ["mobile"]);
        $Insert['UniqueID_SignInCkecked'] =  $SignInCkecked->UniqueID;

        $MobileChecked = MobileChecked::getListObjectByItem($Insert, " invalid_time IS NULL ");
        if (count($MobileChecked) > 0) $MobileChecked->update(["invalid_time" => Tools::getDateTime()]);

        $MobileChecked = Models::insertTable($Insert, "MobileChecked", true);
        $MobileChecked->mobile_code = substr($MobileChecked->UniqueID, -6);
        $MobileChecked->save();

        $Return['MobileChecked'] = $MobileChecked->toArray();
        $Return['ErrorMsg'][] = "系統已發相關驗證碼至您登記的手機號碼，請輸入六位數手機簡訊驗證碼";
        return $Return;
    }

    //手機號碼認證發送
    public function MobileChecked()
    {

        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {

            $Return['ErrorMsg'] = "請先登入";
            return $Return;
        }


        $SignInList = $_SESSION[Tools::getIp()]['SignInList'];

        //讀取身分驗證資料表
        $Item['UniqueID_SignInList'] =  $SignInList['UniqueID'];
        $SignInCkecked = SignInCkecked::getObjectByItem($Item);

        //讀取手機號碼認證資料表
        $Item = Tools::fix_element_Key(self::$PostData, ["UniqueID", "mobile", "mobile_code"]);
        $Item['UniqueID_SignInCkecked'] =  $SignInCkecked->UniqueID;
        $MobileChecked = MobileChecked::getObjectByItem($Item);

        if (empty($MobileChecked->UniqueID)) {

            $Return['ErrorMsg'][] = "簡訊驗證碼，輸入錯誤!";
            return $Return;
        } else {

            $MobileChecked->HTTP_HOST = $_SERVER['HTTP_HOST'];
            $MobileChecked->REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
            $MobileChecked->HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
            $MobileChecked->checked_time = Tools::getDateTime();
            $MobileChecked->save();

            $SignInCkecked->UniqueID_MobileChecked = $MobileChecked->UniqueID;
            $SignInCkecked->mobile_checked_time = Tools::getDateTime();
            $SignInCkecked->save();

           
        }

        $Return['ErrorMsg'][] = "完成手機簡訊驗證";
        return $Return;
    }

    //登入登出功能
    public function Logout()
    {

        if (!empty($_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'])) {
            $UsersLoginLogs = (object) _UniqueID::LoadUniqueIDObject($_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'], "UsersLoginLogs")['Object'];
        } else if (!empty(self::$PostData['UniqueID'])) {
            //判斷還需補強
            $UniqueID = self::$PostData['UniqueID'];
            $UsersLoginLogs = (object) _UniqueID::LoadUniqueIDObject($UniqueID, "UsersLoginLogs")['Object'];
        } else if (!empty($_COOKIE['UniqueID'])) {
            //判斷還需補強
            $UniqueID = self::$PostData['UniqueID'];
            $UsersLoginLogs = (object) _UniqueID::LoadUniqueIDObject($_COOKIE['UniqueID'], "UsersLoginLogs")['Object'];
        } else {
            // 查無登入紀錄 相關UniqueID ，異常呼叫Logout 功能
            $ErrorAction = self::$ActionLogs->toArray();
            $ErrorAction['ErrorMemo'] = "
                查無登入紀錄 相關UniqueID ，異常呼叫Logout 功能 \n
                已存在登出時間，卻還存有登入狀態 \n
            ";
            models::insertTable($ErrorAction, "ErrorAction");

            $Return['ErrorMsg'][] = ['登出異常，請聯絡系統人員'];
            return $Return;
        }



        if (empty($UsersLoginLogs) || !empty($UsersLoginLogs->logout_time)) {
            // 登出例外 ，查無登入紀錄卻有登入ＳＥＳＳＩＯＮ
            // 登出例外 ，已存在登出時間，卻還存有登入狀態
            $ErrorAction = self::$ActionLogs->toArray();
            $ErrorAction['ErrorMemo'] = "
                登出例外 ，查無登入紀錄卻有登入ＳＥＳＳＩＯＮ \n
                已存在登出時間，卻還存有登入狀態 \n
            ";
            models::insertTable($ErrorAction, "ErrorAction");

            $Return['ErrorMsg'][] = ['登出異常，請聯絡系統人員'];
            return $Return;
        } else if (!empty($UsersLoginLogs->UniqueID)) {
            $UsersLoginLogs->logout_time = Tools::getDateTime();
            $UsersLoginLogs->save();

            
        }

        unset($_SESSION[Tools::getIp()]);
        session_destroy();

        $Return['ReDirect'] = "UserSign";
        return $Return;
    }


    public function Login()
    {
        $shortUniqueID = false;
        if (abs(round(((float)microtime(true))) == $_COOKIE['CreateTime'])) {

            $shortUniqueID = _UniqueID::shortUniqueID();
            $_COOKIE['CreateTime'] = $shortUniqueID;
        }

        $Return = [];
        $Insert = Tools::fix_element_Key(self::$PostData, ["account", "mobile", "password"]);
        $UsersLoginLogs = Models::insertTable($Insert, "UsersLoginLogs");
        if (empty($UsersLoginLogs['Object']->UniqueID)) return $UsersLoginLogs;
        else $UsersLoginLogs = $UsersLoginLogs['Object'];



        //會員帳號判斷
        $UsersAccount = Users::getObjectByItem(["account" => $Insert['account'], "password" => $Insert['password']]);

        if (empty($UsersAccount)) $Return['ErrorMsg'][] = "請檢查Email後再登入";
        else $Users = $UsersAccount;

        //會員手機判斷
        $UsersMobile = Users::getObjectByItem(["mobile" => $Insert['mobile'], "password" => $Insert['password']]);

        if (empty($UsersMobile)) $Return['ErrorMsg'][] = "請查明後再登入";
        else $Users = $UsersMobile;


        if (empty($Users->UniqueID)) {
            $Return['ErrorMsg'] = [];
            //註冊帳號判斷
            $SignInListAccount = SignInList::getObjectByItem(["account" => $Insert['account'], "password" => $Insert['password']]);

            if (empty($SignInListAccount)) $Return['ErrorMsg'][] = "請檢查Email後再登入";
            else $SignInList = $SignInListAccount;
            //註冊手機判斷
            $SignInListMobile = SignInList::getObjectByItem(["mobile" => $Insert['mobile'], "password" => $Insert['password']]);

            if (empty($SignInListMobile)) $Return['ErrorMsg'][] = "請查明後再登入";
            else $SignInList = $SignInListMobile;

            if (!empty($SignInList) && !empty($SignInList->Users)) {
                //會員已存在，資料有變更
                $Return['ErrorMsg'][] = "您的登入資訊已有變更，請輸入新的登入資訊，進行登入";
                return $Return;
            } else if (empty($SignInList)) {
                $Return['ErrorMsg'][] = "查無相關資訊，請確認您的登入資訊是否正確";
                return $Return;
            } else {
                unset($Return['ErrorMsg']);
                //顯示相關提醒，身份尚未確認
            }
        } else {

            //從新的會員資訊，找回原本註冊的唯一碼相關資料
            $SignInList = (object) SignInList::getObjectById(["UniqueID" => $Users->UniqueID_SignInList]);
        }






        if (!empty($SignInList->UniqueID)) {

            //確定登入帳密 寫入該次登入紀錄ＩＤ
            $UsersLoginLogs->UniqueID_SignInList = $SignInList->UniqueID;
            $UsersLoginLogs->save();

            //讀取身份驗證資料
            $SignInCkecked = $SignInList->SignInCkecked;
            //已是會員
            if (!empty($Users->UniqueID)) {

                if (!empty($UsersLoginLogs->UniqueID)) $Users->UniqueID_UsersLoginLogs = $UsersLoginLogs->UniqueID;
                else {
                    $Return['ErrorMsg'][] = "登入記錄發生錯誤，請稍後再登入。";
                    return $Return;
                }

                $Item['UniqueID_Users'] = $Users->UniqueID;
                $Remove = UsersLoginLogs::getListObjectByItem($Item);
                $Remove->update(["UniqueID_Users" => "_" . $Users->UniqueID . "_"]);
                //寫入登入紀錄
                $UsersLoginLogs->UniqueID_Users = $Users->UniqueID;
                $UsersLoginLogs->save();
                //寫入會員資料紀錄
                $Users->logined_time = Tools::getDateTime();
                $Users->save();

                //紀錄UsersLocation

                _Api::UsersLocation($Users->UniqueID);
                $_SESSION[Tools::getIp()]['Users'] = $Users->toArray();
            }

            $_SESSION[Tools::getIp()]['SignInSession']['CreateTime'] = $shortUniqueID;
            $_SESSION[Tools::getIp()]['SignInSession']['account'] = $SignInList->account;
            $_SESSION[Tools::getIp()]['SignInSession']['mobile'] = $SignInList->mobile;
            $_SESSION[Tools::getIp()]['SignInSession']['UniqueID'] = $UsersLoginLogs->UniqueID;
            $_SESSION[Tools::getIp()]['SignInSession']['SignInCkecked'] = Tools::fix_element_Key( $SignInCkecked->toArray() ,["email_checked_time","mobile_checked_time","password_checked_time"]);
            
            $_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'] = $UsersLoginLogs->UniqueID;
            $_SESSION[Tools::getIp()]['SignInList'] = $SignInList->toArray();

            $_COOKIE['user_md5'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
            $Return['UniqueID'] = $UsersLoginLogs->UniqueID;
        } else {
            $Return['ErrorMsg'][] = "登入資訊有誤，請查明後再登入";
        }

        if (!empty($Return['ErrorMsg'])) {
            $UsersLoginLogs->login_status = join(",", $Return['ErrorMsg']);
            $UsersLoginLogs->save();
        } else {
            //預設登入後頁面 List_bargain
            $_SESSION[Tools::getIp()]['History'] = "UserSign";
            $_SESSION[Tools::getIp()]['ReDirect'] = "UserInfo";
        }

        return $Return;
    }

    //登入狀態
    public function UsersLoginLogs()
    {
        if (empty(self::$PostData['UniqueID'])) {
            $this->Logout();
        } else if (_UniqueID::checkPublicUniqueID(self::$PostData['UniqueID'])) {
            $UniqueID = _UniqueID::privateUniqueID(self::$PostData['UniqueID']);
        } else  $UniqueID = self::$PostData['UniqueID'];

        if (empty($_SESSION[Tools::getIp()])) {
            $this->Logout();
        }

        if ($UniqueID == $_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'])
            $_COOKIE['user_md5'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        else $this->Logout();

        $Return['UniqueID'] =  _UniqueID::publicUniqueID($UniqueID);
        return  $Return;
    }

    public function MessageBox()
    {

        $UniqueID = _UniqueID::privateUniqueID((string) $this->UsersLoginLogs()['UniqueID']);
        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {
            $Return['ErrorMsg'][] = "請先登入";
            return $Return;
        }

        if (empty(self::$PostData['MessageType']['UniqueID_UsersLoginLogs'])) {
            $Return['ErrorMsg'][] = "訊息功能無法使用";
            return $Return;
        }

        if (!empty(self::$PostData['Message'])) $Message = self::$PostData['Message'];

        
        $Item = [];
        $Item['UniqueID_UsersLoginLogs'] = self::$PostData['MessageType']['UniqueID_UsersLoginLogs'];
        $Item['action_token'] = self::$PostData['MessageToken'];


        $MessageToken = MessageToken::getObjectByItem($Item, " faild_time IS NULL AND used_time IS NULL ");
        if (!empty($MessageToken->UniqueID) && Tools::checkToken($MessageToken->action_token,  $MessageToken->UniqueID_MessageType . $MessageToken->UniqueID_UsersLoginLogs . $MessageToken->shortUniqueID_SwooleConnections)) {
            $MessageToken->used_time = Tools::getDateTime();
            $MessageToken->save();

            $Return['MessageToken'] = $MessageToken->toArray();
        } else {
            $Return['ErrorMsg'][] = "訊息功能無法使用";
        }

        $Insert = [];
        $Insert['UniqueID_UsersLoginLogs'] = $UniqueID;
        $Insert['UniqueID_MessageType'] = $MessageToken->UniqueID_MessageType;
        $UserTempMessages = Models::insertTable($Insert, "UserTempMessages", true);
        $MessageDataFile = (object) null;

        //建立訊息暫存資料
        if (!empty($UserTempMessages->UniqueID)) {
            //建立訊息檔案
            $points_cost = 30;
            $points_off = 30;

            $encrypted_data['MessageType'] = self::$PostData['MessageType'];
            $encrypted_data['points_cost'] = $points_cost;
            $encrypted_data['points_off'] = $points_off;
            $encrypted_data['encrypted_data'] = $Message;


            $Insert = [];
            $Insert['UniqueID_UserTempMessages'] = $UserTempMessages->UniqueID;
            $Insert['encrypted_data'] = $encrypted_data;
            $MessageDataFile = Models::insertTable($Insert, "MessageDataFile", true);

            if (!empty($MessageDataFile->UniqueID)) {
                $UserTempMessages->UniqueID_MessageDataFile = $MessageDataFile->UniqueID;
                $UserTempMessages->save();
            } else {
                $Return['ErrorMsg'][] = "訊息建立失敗";
                return $Return;
            }

            $Return['UserTempMessages'] = $UserTempMessages->toArray();
        } else {
            $Return['ErrorMsg'][] = "訊息發送異常，請重新發送";
            return $Return;
        }

        //判斷發送對象，是否在線上
        if (!empty(self::$PostData['MessageUsers'])) $MessageUsers = Tools::fix_array_Key(self::$PostData['MessageUsers'], "shortUniqueID");


        if (!empty($MessageUsers))
            foreach ($MessageUsers as $shortUniqueID) {


                $UsedMessagePoints = _UsersApi::insertUsedMessagePoints($MessageDataFile, $encrypted_data['points_cost'], $encrypted_data['points_off']);


                $Item = [];
                $Item['shortUniqueID']  = $shortUniqueID;

                $SwooleConnections = SwooleConnections::getObjectByItem($Item);
                if (!empty($MessageDataFile->UniqueID) &&  !empty($SwooleConnections->UniqueID)  && !empty($SwooleConnections->UsersLoginLogs)) {

                    $UsersLoginLogs = $SwooleConnections->UsersLoginLogs;



                    $Insert = [];
                    $Insert['UniqueID_MessageDataFile'] = $MessageDataFile->UniqueID;
                    $Insert['UniqueID_SignInList'] = $_SESSION[Tools::getIp()]['SignInList']['UniqueID'];
                    $Insert['UniqueID_MessageToken'] = $MessageToken->UniqueID;
                    if (!empty($_SESSION[Tools::getIp()]['Users']))
                        $Insert['UniqueID_Users'] = $_SESSION[Tools::getIp()]['Users']['UniqueID'];


                    $MessageToUsers = models::insertTable($Insert, "MessageToUsers", true);


                    if (!empty($MessageToUsers->UniqueID)) {

                        $Return['MessageToUsers'][] = $MessageToUsers->toArray();
                    }

                    if (empty($UsersLoginLogs->SignInList)) {
                        //查無對象註冊資訊

                        $Insert = [];
                        $Insert['UniqueID_SignInList'] = $_SESSION[Tools::getIp()]['SignInList']['UniqueID'];
                        if (!empty($_SESSION[Tools::getIp()]['Users']))
                            $Insert['UniqueID_Users'] = $_SESSION[Tools::getIp()]['Users']['UniqueID'];
                        $Insert['UniqueID_MessageType'] = $MessageToken->UniqueID_MessageType;
                        $Insert['UniqueID_UserTempMessages'] = $UserTempMessages->UniqueID;
                        $Insert['UniqueID_UserMessageSchedule'] = NULL;
                        if (!empty($MessageToUsers->UniqueID))
                            $Insert['UniqueID_MessageToUsers'] = $MessageToUsers->UniqueID;
                        $Insert['UniqueID_MessageDataFile'] = $MessageDataFile->UniqueID;
                        $Insert['UniqueID_UsedMessagePoints'] = $UsedMessagePoints['UsedMessagePoints']['UniqueID'];

                        $MessageUniqueidRecord = models::insertTable($Insert, "MessageUniqueidRecord");

                        if (!empty($MessageUniqueidRecord->UniqueID)) {
                            $Return['MessageUniqueidRecord'][] = $MessageUniqueidRecord->toArray();
                        }
                    } else if (!empty($MessageToUsers->UniqueID)) {
                        $Insert = [];
                        $Insert['UniqueID_MessageToUsers'] = $MessageToUsers->UniqueID;
                        $Insert['UniqueID_SignInList'] = $UsersLoginLogs->SignInList->UniqueID;
                        if (!empty($UsersLoginLogs->Users))
                            $Insert['UniqueID_Users'] = $UsersLoginLogs->Users->UniqueID;
                        $MessageReceiver = models::insertTable($Insert, "MessageReceiver");
                    }

                    //使用者不在線上 將送往排程紀錄，並回傳建立的UniqueID 當作client 端備用紀錄
                    if (!empty($SwooleConnections->logout_time)) {
                        
                        $Insert = [];
                        $Insert['UniqueID_UserTempMessages'] = $UserTempMessages->UniqueID;

                        $Insert['UniqueID_MessageType'] = $MessageToken->UniqueID_MessageType;
                        $Insert['UniqueID_MessageToUsers'] = $MessageToUsers->UniqueID;

                        if (!empty($_SESSION[Tools::getIp()]['Users']))
                            $Insert['UniqueID_Users'] = $_SESSION[Tools::getIp()]['Users']['UniqueID'];

                        $UserMessageSchedule  = models::insertTable($Insert, "UserMessageSchedule");

                        if (!empty($UserMessageSchedule->UniqueID)) {
                            $Return['UserMessageSchedule'][] = $UserMessageSchedule->UniqueID;
                        }
                    }
                } else {

                    //查無訊息 連線資料
                    $Insert = [];
                    $Insert['UniqueID_SignInList'] = $_SESSION[Tools::getIp()]['SignInList']['UniqueID'];
                    if (!empty($_SESSION[Tools::getIp()]['Users']))
                        $Insert['UniqueID_Users'] = $_SESSION[Tools::getIp()]['Users']['UniqueID'];
                    $Insert['UniqueID_MessageType'] = $MessageToken->UniqueID_MessageType;
                    $Insert['UniqueID_UserTempMessages'] = $UserTempMessages->UniqueID;
                    $Insert['UniqueID_UserMessageSchedule'] = NULL;
                    $Insert['UniqueID_MessageToUsers'] = NULL;
                    $Insert['UniqueID_MessageDataFile'] = $MessageDataFile->UniqueID;
                    $Insert['UniqueID_UsedMessagePoints'] = $UsedMessagePoints['UsedMessagePoints']['UniqueID'];

                    $MessageUniqueidRecord = models::insertTable($Insert, "MessageUniqueidRecord");

                    if (!empty($MessageUniqueidRecord->UniqueID)) {
                        $Return['MessageUniqueidRecord'][] = $MessageUniqueidRecord->toArray();
                    }
                }

                $Return['UsedMessagePoints'][] =  $UsedMessagePoints;
            }



        return $Return;
    }

    public function MessageToUsers()
    {

        $UniqueID = _UniqueID::privateUniqueID((string) $this->UsersLoginLogs()['UniqueID']);

        $UniqueID_MessageToken = self::$PostData['UniqueID_MessageToken'];

        $Item = [];
        $Item['UniqueID_MessageToken'] = $UniqueID_MessageToken;

        $MessageToUsers = MessageToUsers::getObjectByItem($Item);

        $MessageToUsers->close_time = Tools::getDateTime();
        $MessageToUsers->save();

        $Return[__FUNCTION__] = $MessageToUsers->toArray();

        return $Return;
    }
    public function UserMessageUsed()
    {
        $UniqueID = _UniqueID::privateUniqueID((string) $this->UsersLoginLogs()['UniqueID']);

        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {

            $Return['ErrorMsg'] = "請先登入";
            return $Return;
        }


        $UniqueID_MessageToUsers = self::$PostData['UniqueID_MessageToUsers'];
        $UniqueID_MessageDataFile = self::$PostData['UniqueID_MessageDataFile'];

        $Item = [];
        $Item['UniqueID'] = $UniqueID_MessageDataFile;

        $MessageDataFile = MessageDataFile::getObjectByItem($Item);

        if (!empty($MessageDataFile->UniqueID)) {
            $Insert = [];
            $Insert['UniqueID_SignInList'] = $_SESSION[Tools::getIp()]['SignInList']['UniqueID'];
            $Insert['UniqueID_MessageToUsers'] = $UniqueID_MessageToUsers;


            if (!empty($_SESSION[Tools::getIp()]['Users']))
                $Insert['UniqueID_Users'] = $_SESSION[Tools::getIp()]['Users']['UniqueID'];

            $UserMessageUsed = models::insertTable($Insert, "UserMessageUsed", true);
        }

        if (!empty($UserMessageUsed->UniqueID))
            $Return['UserMessageUsed'] = $UserMessageUsed->toArray();

        $Return['MessageDataFile'] = $MessageDataFile->toArray();

        return $Return;
    }
    public function ReMessage()
    {
        if (empty($_SESSION[Tools::getIp()]['SignInList'])) {

            $Return['ErrorMsg'] = "請先登入";
            return $Return;
        }

        $UniqueID = _UniqueID::privateUniqueID((string) $this->UsersLoginLogs()['UniqueID']);

        $SignInList = $_SESSION[Tools::getIp()]['SignInList'];

        //讀取身分驗證資料表
        $Item['UniqueID_SignInList'] =  $SignInList['UniqueID'];
        $SignInCkecked = SignInCkecked::getObjectByItem($Item);

        //設定資料

        $ReUniqueID = self::$PostData['ReUniqueID'];
        $ReMessage = self::$PostData['ReMessage'];

        $Insert = [];
        $Insert['UniqueID_UsersLoginLogs'] = $UniqueID;
        $UserTempMessages = Models::insertTable($Insert, "UserTempMessages", true);

        if (!empty($UserTempMessages->UniqueID)) {
            $Insert = [];
            $Insert['UniqueID_UserTempMessages'] = $UserTempMessages->UniqueID;
            $Insert['encrypted_data'] = $ReMessage;
            $MessageDataFile = Models::insertTable($Insert, "MessageDataFile", true);

            if (!empty($MessageDataFile->UniqueID)) {
                $UserTempMessages->UniqueID_MessageDataFile = $MessageDataFile->UniqueID;
                $UserTempMessages->save();
            } else {
                $Return['ErrorMsg'][] = "訊息建立失敗";
                return $Return;
            }

            $Return['UserTempMessages'] = $UserTempMessages->toArray();
            return $Return;
        } else {
            $Return['ErrorMsg'][] = "訊息發送異常，請重新發送";
            return $Return;
        }
    }
    //顯示會員足跡


    public function SignInList()
    {


        $UsersLoginLogs = $this->UsersLoginLogs();



        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["label"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select);


        $Return = [];
        $Return[$TableName] = _UsersApi::$TableName($TableName::find($Select));


        return $Return;
    }
    public function UserTempMessages()
    {
        $UsersLoginLogs = $this->UsersLoginLogs();



        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["label"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']) . " AND UniqueID_UsersLoginLogs = '{$UsersLoginLogs['UniqueID']}' ";
        } else $Select = " UniqueID_UsersLoginLogs = '{$UsersLoginLogs['UniqueID']}' ";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select);


        $Return = [];
        $Return[$TableName] = _UsersApi::$TableName($TableName::find($Select));


        return $Return;
    }


    public function UserFootPoint()
    {
        $UniqueID = _UniqueID::privateUniqueID((string) $this->UsersLoginLogs()['UniqueID']);



        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["label"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']) . " AND UniqueID_UsersLoginLogs = '{$UniqueID}' ";
        } else $Select = " UniqueID_UsersLoginLogs = '{$UniqueID}' ";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select);


        $Return = [];
        $Return[$TableName] = _UsersApi::$TableName($TableName::find($Select));


        return $Return;
    }
    //顯示會員列表

    public function Users()
    {

        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["Controller", "Action", "HTTP_HOST", "REMOTE_ADDR"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select);


        $Return = [];
        $Return[$TableName] = _UsersApi::$TableName($TableName::find($Select));


        return $Return;
    }
}
