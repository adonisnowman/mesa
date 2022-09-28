<?php

use Phalcon\Http\Response;

class UsersApiController extends BaseController
{


    public static $arrContextOptions = [];
    public static $PostData = [];
    public static $checkKeys = [];
    public static $Pages = [];

    public function initialize()
    {
        $some_name = session_name("some_name");
        session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST']);

        session_start();
        self::$arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

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
        $Item["ActionPort"] = _Action::ActionPort($_SERVER['HTTP_ORIGIN'],$Item);
        $checkKeys = checkKeys::getObjectByItem($Item);
        
        if (!empty($checkKeys) && $checkKeys->offshelf) {
            $Return['ErrorMsg'][] = "Api Closed";
            echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
            exit;
        }else if(empty($checkKeys)){

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
        else $ActionLogs = $Return['Object'];

        $Return = $this->$Action();
        $Return['Action'] = $Action;
        $Return['Pages'] = self::$Pages;

        if (!empty($checkKeys->UniqueID)) {
            $checkKeys->called_time = Tools::getDateTime();
            $checkKeys->save();
        }

        if (!empty($ActionLogs->UniqueID)) {
            //這邊不在讀取資料表判斷
            if (!empty($_SESSION[Tools::getIp()])) {
                $ActionLogs->UniqueID_UsersLoginLogs = $_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'];
                $ActionLogs->UniqueID_Users = $_SESSION[Tools::getIp()]['UniqueID'];
            } else if (!empty(self::$PostData['UniqueID'])) {
                $ActionLogs->UniqueID_UsersLoginLogs = self::$PostData['UniqueID'];
            }

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
            if (!empty($_SESSION[Tools::getIp()]['ReDirect'])) $Return['ReDirect'] = "reload";
            else $Return['ReDirect'] = $History;
        }
        echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
    }

    //導頁
    public function Redirect()
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

    //註冊
    public function Sign()
    {
        
        $Insert = Tools::fix_element_Key(self::$PostData, ["account", "mobile","password"]);
        
        //會員帳號判斷
        $Users = Users::getObjectByItem(["account" => $Insert['account']]);

        if ( !empty($Users->account)) {

            $Return['ErrorMsg'][] = "會員已註冊過了";
            return $Return ;
            
        }else {
            //新增會員
            $Users = Models::insertTable($Insert, "Users", true);

            if(empty($Users->UniqueID)) return $Users;
            
            $_Views = _Views::Init();
            $_Views['ReDirect'] = "signEmail";
            $_Views['UniqueID'] = $Users->UniqueID;
            $_Views['Token'] = Tools::getToken();
            
            Tools::emailSend($Insert['account'],"signEmail",_Views::RedirectAdmin($_Views));
            $Return['ErrorMsg'][] = "請收您的驗證信件，點擊完成註冊";
            return $Return ;
        }
    }

    //登入登出功能
    public function Logout()
    {
        unset($_SESSION[Tools::getIp()]['ReDirect']);
        session_destroy();
        $Return['ReDirect'] = "UserSign";
        return $Return;
    }


    public function Login()
    {        
        $Return = [];
        $Insert = Tools::fix_element_Key(self::$PostData, ["account", "mobile","password"]);
        $UsersLoginLogs = Models::insertTable($Insert, "UsersLoginLogs");
        if (empty($UsersLoginLogs['Object']->UniqueID)) return $UsersLoginLogs;
        else $UsersLoginLogs = $UsersLoginLogs['Object'];

        //會員帳號判斷
        $Users = Users::getObjectByItem(["account" => $Insert['account']]);

        if (empty(Users::count()) && empty($Users->account)) {
            //新增會員
            $Users = Models::insertTable($Insert, "Users", true);
        }


        //會員帳號判斷
        $Users = Users::getObjectByItem(["account" => $Insert['account']]);

        if (!empty($Users->password) && $Users->password == $Insert['password']) {
            //確定登入帳密 寫入該次登入紀錄ＩＤ
            if (!empty($UsersLoginLogs->UniqueID)) $Users->UniqueID_UsersLoginLogs = $UsersLoginLogs->UniqueID;
            //寫入登入紀錄
            $UsersLoginLogs->UniqueID_account = $Users->UniqueID;
            $UsersLoginLogs->save();
            //寫入會員資料紀錄
            $Users->logined_time = Tools::getDateTime();
            $Users->save();


            $_SESSION[Tools::getIp()] = $Users->toArray();
            $_SESSION['checkKeys'] = checkKeys::getListByItem(["AccountType" => "None"]);
            $_SESSION['checkKeys'] = array_merge($_SESSION['checkKeys'], checkKeys::getListByItem(["AccountType" => $Users->account]));


            $Return['UniqueID'] = $Users->UniqueID;




            //紀錄UsersLocation

            _Api::UsersLocation($Users->UniqueID);
        } else if (!empty($Users->account)) {
            $Return['ErrorMsg'][] = "password error";
        } else {
            $Return['ErrorMsg'][] = "post error";
        }

        if (!empty($Return['ErrorMsg'])) {
            $UsersLoginLogs->login_status = join(",", $Return['ErrorMsg']);
            $UsersLoginLogs->save();
        } else {
            //預設登入後頁面 List_bargain
            $_SESSION[Tools::getIp()]['History'] = "UserSign";
            $_SESSION[Tools::getIp()]['ReDirect'] = "Input_checkKeysRule";
        }

        return $Return;
    }
}
