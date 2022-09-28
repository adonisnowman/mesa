<?php

use Phalcon\Http\Response;

class ListController extends BaseController
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
            if (!empty($_SESSION[Tools::getIp()]['ReDirect'])) $Return['ReDirect'] = "reload";
            else $Return['ReDirect'] = $History;
        }
        echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
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

    
}
