<?php

class _Api
{

    public static $app_id;
    public static $arrContextOptions = [];
    function __construct()
    {
        self::$arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );
    }
    
    public static function AccountsLocation($UniqueID_Accounts){

        $Item = Tools::fix_element_Key($_SERVER,["HTTP_HOST","REMOTE_ADDR"]);
        $AccountsLocation = AccountsLocation::getObjectByItem($Item);
        $Accounts = Accounts::getObjectById(["UniqueID"=>$UniqueID_Accounts]);
        if(empty($AccountsLocation->UniqueID) && !empty($Accounts->UniqueID)){
            $Insert = Tools::fix_element_Key($_SERVER,["HTTP_HOST","REMOTE_ADDR","HTTP_USER_AGENT"]);
            $Insert['UniqueID_Accounts'] = $Accounts->UniqueID;
            $Return = Models::insertTable($Insert,"AccountsLocation");           
            return $Return;
        }
    }

    public static function UsersLocation($UniqueID_Users){

        $Item = Tools::fix_element_Key($_SERVER,["HTTP_HOST","REMOTE_ADDR"]);
        $UsersLocation = UsersLocation::getObjectByItem($Item);
        $Users = Users::getObjectById(["UniqueID"=>$UniqueID_Users]);
        if(empty($UsersLocation->UniqueID) && !empty($Users->UniqueID)){
            $Insert = Tools::fix_element_Key($_SERVER,["HTTP_HOST","REMOTE_ADDR","HTTP_USER_AGENT"]);
            $Insert['UniqueID_Users'] = $Users->UniqueID;
            $Return = Models::insertTable($Insert,"UsersLocation");           
            return $Return;
        }
    }



    public static function HTTP_HOST_Check() {

        $Insert['HTTP_HOST'] = trim($_SERVER["HTTP_HOST"]);
        $ServersLocation = ServersLocation::getObjectByItem($Insert);
        if(!empty($ServersLocation->UniqueID)) $ServersLocation->called_time = Tools::getDateTime();
        else return Models::insertTable($Insert,"ServersLocation");        

    }
    public static function checkPostData($checkKeys)
    {
        
        extract($_POST, EXTR_OVERWRITE, "");

        //存在資料 轉成JSON
        if (!empty($Data)) $Data = json_decode($Data, true);
        else   $aResult['ErrorMsg'][] = "參數有誤";
        
       
        //存在資料 轉成JSON 是ＡＲＲＡＹ 判斷所需輸入資料是否符合
        if (!empty($checkKeys) && is_array($Data)) $result = array_diff($checkKeys, array_keys($Data));
        else $aResult['ErrorMsg'][] = "資料格式有誤";        
       
        if (!empty($result)) $aResult['ErrorMsg'][] = join(",",$result);  
        
        //將前台unique_id 轉成後台使用的 UniqueID
        if(!empty($Data['UniqueID']) && _UniqueID::checkPublicUniqueID($Data['UniqueID']))
            $Data['UniqueID'] = _UniqueID::privateUniqueID($Data['unique_id']);
        else if (isset($Data['unique_id']) && !isset($Data['UniqueID']) && 
                _UniqueID::checkPublicUniqueID($Data['unique_id'])) 
            $Data['UniqueID'] = _UniqueID::privateUniqueID($Data['unique_id']);
        

        if (!empty($Data['UniqueID']) && false == _UniqueID::checkUniqueID($Data['UniqueID'])) {
            $aResult['ErrorMsg'][] = "UniqueID 不存在[{$Data['UniqueID']}]";
            $aResult['ReDirect'] = "Logout";
        }
        if (!empty($aResult['ErrorMsg'])) {
            echo JSON_encode( $aResult, JSON_UNESCAPED_UNICODE);
            exit;
        }
        return $Data;
    }
    
    public static function InuptTable($tableName, $PostData,$checkKeys,$UniqueKeys,$Rule){
        
       
        $Insert = Tools::fix_element_Key($PostData,$checkKeys);
        unset($Insert['UniqueID']);
        
        $$tableName = $tableName::getObjectByItem(Tools::fix_element_Key($PostData,$UniqueKeys));
        if(empty($$tableName->UniqueID) && in_array("extend_insert",$Rule)) {
            $Return = Models::insertTable($Insert,$tableName);            
        }
        else if (in_array("extend_update",$Rule)){
            $Return['Data']['action'] = "Update";
            $$tableName->assign($Insert);
            $$tableName->save();
            foreach ($$tableName->getMessages() as $message) {
                $Return['SystemMsg'][] = $message;
              }
            $Return['Data'][$tableName] = $$tableName->toArray();
        }else {
            $Return['ErrorMsg'] = " Permission denied ";
        }

        return $Return;
    }

    //讀取資料格式

    public static function RedirectAdmin($Ojbects){
        if(empty($Ojbects)) $Return = [];
        else {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $Item['offshelf'] = (!empty($Item['offshelf']));
                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    public static function ActionLogs($Ojbects){
        if(empty($Ojbects)) $Return = [];
        else {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $Item['PostData_JSON'] = "";
                $Item['Return_JSON'] = "";
                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    public static function Accounts($Ojbects){
        if(empty($Ojbects)) $Return = [];
        else {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $Item['offshelf'] = (!empty($Item['offshelf']));
                $Item['AllowIps'] = explode(",",$Item['AllowIps']);
                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    
    public static function checkKeys($Ojbects){
        if(empty($Ojbects)) $Return = [];
        else {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $RuleCheck = explode(",",$Item['Rule']);           
                $Rule['extend_group'] = in_array("extend_group",$RuleCheck);
                $Rule['extend_remote'] = in_array("extend_remote",$RuleCheck);
                $Rule['extend_insert'] = in_array("extend_insert",$RuleCheck);
                $Rule['extend_update'] = in_array("extend_update",$RuleCheck);

                $Item['Rule'] = $Rule;
                $Item['PostData'] = explode(",",$Item['PostData']);
                $Item['extends_disable'] = (!empty($Item['extends_disable']));
                $Item['offshelf'] = (!empty($Item['offshelf']));
                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    public static function checkKeysRule($Ojbects){

        $Return = [];
        if(!empty($Ojbects)) {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $Item['checkKeys'] = _UniqueID::loadUniqueID($Item['UniqueID_checkKeys'])['data'];
                $RuleCheck = explode(",",$Item['checkKeys']['Rule']);
                $Rule['extend_group'] = in_array("extend_group",$RuleCheck);
                $Rule['extend_remote'] = in_array("extend_remote",$RuleCheck);
                $Rule['extend_insert'] = in_array("extend_insert",$RuleCheck);
                $Rule['extend_update'] = in_array("extend_update",$RuleCheck);

                $Item['checkKeys']['Rule'] = $Rule;
                $Item['checkKeys']['PostData'] = explode(",",$Item['checkKeys']['PostData']);
                $Item['AccountGroups'] = explode(",",$Item['AccountGroups']);
                $Item['Users'] = explode(",",$Item['Users']);
                $Item['offshelf'] = (!empty($Item['offshelf']));
                $Item['Accounts'] = _UniqueID::loadUniqueID($Item['UniqueID_Accounts'])['data'];
            
                $Item['admin_disable'] = (!empty($Item['admin_disable']));
                $Item['account_disable'] = (!empty($Item['account_disable']));
                $Item['extends_disable'] = (!empty($Item['extends_disable']));
                
                $Item['Rule'] = explode(",",$Item['Rule']);
                $Rule['extend_group'] = ($Rule['extend_group'] && in_array("extend_group",$Item['Rule']));
                $Rule['extend_remote'] = ($Rule['extend_remote'] && in_array("extend_remote",$Item['Rule']));
                $Rule['extend_insert'] = ($Rule['extend_insert'] && in_array("extend_insert",$Item['Rule']));
                $Rule['extend_update'] = ($Rule['extend_update'] && in_array("extend_update",$Item['Rule']));
                $Item['Rule'] = $Rule;
                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    // 寫入資料格式
    public static function Input_checkKeys($PostData){
        $PostData['PostData'] = join(",",$PostData['PostData']);
       
        if(!empty($PostData['extends_disable']) && !empty($PostData['Rule'])){
            $PostData['Rule'] = join(",",array_keys(array_filter($PostData['Rule'], function($k) {
                return $k ;
            })));
        }else $PostData['Rule'] = NULL;

        return $PostData;
    }


    public static function Input_checkKeysRule($PostData){
        if(!empty($PostData['extends_disable']) && !empty($PostData['Rule'])){
            $PostData['Rule'] = join(",",array_keys(array_filter($PostData['Rule'], function($k) {
                return $k ;
            })));
        }else $PostData['Rule'] = NULL;
        
        if(in_array("extend_update",explode(",",$PostData['Rule']))){
            $PostData['PostData'] = join(",",$PostData['PostData']);            
        }else $PostData['PostData'] = NULL;

        $PostData['UniqueID_Accounts'] = $PostData['Accounts']['UniqueID'];
        $PostData['REMOTE_ADDR']= $PostData['Accounts']['REMOTE_ADDR'];
        $PostData['AccountGroups'] = join(",",$PostData['AccountGroups']);
        $PostData['Users'] = join(",",$PostData['Users']);

        if(empty($PostData['AccountGroups'])) $PostData['AccountGroups'] = "Default";
        if(empty($PostData['Users'])) $PostData['Users'] = "Default";
        
        return $PostData;
    }

    public static function Input_Accounts($PostData){
        $PostData['offshelf'] = ($PostData['offshelf'])?1:0;
        $PostData['AllowIps'] = join(",",$PostData['AllowIps']);
        return $PostData;
    }

    public static function Input_RedirectAdmin($PostData){
        $PostData['offshelf'] = ($PostData['offshelf'])?1:0;

        return $PostData;
    }
    
}
