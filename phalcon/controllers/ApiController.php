<?php

use Phalcon\Http\Response;

class ApiController extends BaseController
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
    public function checkImage()
    {
        $code = self::$PostData['code'];
        $ImageCode = self::$PostData['ImageCode'];
        $Return = [];
        if (Tools::Crypt($ImageCode, true) == $code) {
            $Return['Msg'] = "SUCCESS";
        } else {
            $Return['Msg'] = "FAILD";
        }
        return $Return;
    }

    public function getImage()
    {
        $number = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
        shuffle($number);
        $code = substr(join("", $number), 0, 4);
        $Return['Msg'] = "SUCCESS";
        $Return['Data']['ImageCode'] = Tools::Crypt($code);
        return $Return;
    }

    public function Dashboard()
    {
        $BaseModel = new BaseModel;
        $BaseModel = $BaseModel->findQuery(" SELECT UniqueID_checkKeys,COUNT(UniqueID_checkKeys) as num FROM ActionLogs GROUP BY UniqueID_checkKeys ");
        $BaseModel = $BaseModel->toArray();
        foreach ($BaseModel as &$Item) {
            $Item['checkKeys'] = _UniqueID::loadUniqueID($Item['UniqueID_checkKeys'])['data'];

            $Pie['region'] = $Item['checkKeys']['ControllerName'] . "_" . $Item['checkKeys']['ActionName'];
            $Pie['val'] = (int)$Item['num'];
            $Pie_List[] = $Pie;
        }

        $Return['Dashboard'] = $Pie_List;
        return $Return;
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
            if (!empty($_SESSION['Accounts'])) {
                $ActionLogs->UniqueID_AccountsLoginLogs = $_SESSION['Accounts']['UniqueID_AccountsLoginLogs'];
                $ActionLogs->UniqueID_Accounts = $_SESSION['Accounts']['UniqueID'];
            } else if (!empty(self::$PostData['UniqueID'])) {
                $ActionLogs->UniqueID_AccountsLoginLogs = self::$PostData['UniqueID'];
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
            if (Tools::getIp() == Tools::ServerIp()){
                if (!empty($_SESSION['Admin']['ReDirect'])) $Return['ReDirect'] = "reload";
                else $Return['ReDirect'] = $History;
            }else {
                if (!empty($_SESSION['User']['ReDirect'])) $Return['ReDirect'] = "reload";
                else $Return['ReDirect'] = $History;
            }
           
            
        }
        echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
    }


    //讀取單筆資料
    public function ActionLogsData()
    {

        $Insert = Tools::fix_element_Key(self::$PostData, ['UniqueID']);
        $Return['ActionLogsData'] = _UniqueID::loadUniqueID($Insert['UniqueID'])['data'];
        return $Return;
    }

    public function AccountsData()
    {
        $Insert = Tools::fix_element_Key(self::$PostData, ['UniqueID']);

        $Item = _UniqueID::loadUniqueID($Insert['UniqueID'])['data'];
        $Return['AccountsData'] = $Item;
        return $Return;
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

    //選項列表
    public function AccountTypes()
    {

        $Return = [];
        $Accounts = Accounts::find(["", "columns" => "account"]);
        $Return['AccountTypes'] = Tools::fix_array_Key($Accounts->toArray(), "account");
        return $Return;
    }

    

    //列表資料

    public function checkKeysRule()
    {

        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["UniqueID_Accounts"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select, $TableName::count());


        $Return = [];
        $Return[$TableName] = _Api::$TableName($TableName::find($Select));


        return $Return;
    }

    public function checkKeys()
    {

        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["ReDirect", "ViewsPath", "ViewsType"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select, $TableName::count());


        $Return = [];
        $Return[$TableName] = _Api::$TableName($TableName::find($Select));


        return $Return;
    }



    public function RedirectAdmin()
    {

        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["ReDirect", "ViewsPath", "ViewsType"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select, $TableName::count());
        $Select['order']="ViewsPath DESC";

        $Return = [];
        $Return[$TableName] = _Api::$TableName($TableName::find($Select));


        return $Return;
    }
    public function ActionLogs()
    {

        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["Controller", "Action", "HTTP_HOST", "REMOTE_ADDR"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select);


        $Return = [];
        $Return[$TableName] = _Api::$TableName($TableName::find($Select));


        return $Return;
    }

    public function Accounts()
    {

        $TableName = __FUNCTION__;

        if (!empty(self::$PostData['Search'])) {
            $SelectKeys = ["account"];
            $Select = Models::SelectLike($SelectKeys, self::$PostData['Search']);
        } else $Select = "";
        self::$Pages = Tools::Pages(self::$Pages, $TableName::count());
        $Select = Models::DefultSelect(self::$Pages, $Select);


        $Return = [];
        $Return[$TableName] = _Api::$TableName($TableName::find($Select));


        return $Return;
    }

    //登入登出功能
    public function Logout()
    {
        unset($_SESSION[Tools::getIp()]['ReDirect']);
        session_destroy();
        $Return['ReDirect'] = "sign-in";
        return $Return;
    }


    public function Login()
    {        
        $Return = [];
        $Insert = Tools::fix_element_Key(self::$PostData, ["account", "password"]);
        $AccountsLoginLogs = Models::insertTable($Insert, "AccountsLoginLogs");
        if (empty($AccountsLoginLogs['Object']->UniqueID)) return $AccountsLoginLogs;
        else $AccountsLoginLogs = $AccountsLoginLogs['Object'];

        //會員帳號判斷
        $Accounts = Accounts::getObjectByItem(["account" => $Insert['account']]);

        if (empty(Accounts::count()) && empty($Accounts->account)) {
            //新增會員
            $Accounts = Models::insertTable($Insert, "Accounts", true);
        }


        //會員帳號判斷
        $Accounts = Accounts::getObjectByItem(["account" => $Insert['account']]);

        if (!empty($Accounts->password) && $Accounts->password == $Insert['password']) {
            //確定登入帳密 寫入該次登入紀錄ＩＤ
            if (!empty($AccountsLoginLogs->UniqueID)) $Accounts->UniqueID_AccountsLoginLogs = $AccountsLoginLogs->UniqueID;
            //寫入登入紀錄
            $AccountsLoginLogs->UniqueID_account = $Accounts->UniqueID;
            $AccountsLoginLogs->save();
            //寫入會員資料紀錄
            $Accounts->logined_time = Tools::getDateTime();
            $Accounts->save();


            $_SESSION['Accounts'] = $Accounts->toArray();
            $_SESSION['checkKeys'] = checkKeys::getListByItem(["AccountType" => "None"]);
            $_SESSION['checkKeys'] = array_merge($_SESSION['checkKeys'], checkKeys::getListByItem(["AccountType" => $Accounts->account]));


            $Return['UniqueID'] = $Accounts->UniqueID;




            //紀錄AccountsLocation

            _Api::AccountsLocation($Accounts->UniqueID);
        } else if (!empty($Accounts->account)) {
            $Return['ErrorMsg'][] = "password error";
        } else {
            $Return['ErrorMsg'][] = "post error";
        }

        if (!empty($Return['ErrorMsg'])) {
            $AccountsLoginLogs->login_status = join(",", $Return['ErrorMsg']);
            $AccountsLoginLogs->save();
        } else {
            //預設登入後頁面 List_bargain
            $_SESSION['History'] = "sign-in";
            $_SESSION[Tools::getIp()]['ReDirect'] = "Input_checkKeysRule";
        }

        return $Return;
    }
}
