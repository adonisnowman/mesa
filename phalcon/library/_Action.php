<?php

class _Action
{

    public static $Insert = [];
    public static function ActionLogs($router, $PostData, BaseModel $checkKeys = NULL)
    {
        self::$Insert = Tools::fix_element_Key($_SERVER, ["HTTP_HOST", "REMOTE_ADDR", "HTTP_USER_AGENT"]);
        self::$Insert['Controller'] = $router->getControllerName();
        if (!empty($checkKeys)) {
            self::$Insert['Action'] = $checkKeys->ActionName;
            self::$Insert['UniqueID_checkKeys'] = $checkKeys->UniqueID;
        }
        self::$Insert['PostData_JSON'] = JSON_encode($PostData, JSON_UNESCAPED_UNICODE);

        return self::$Insert;
    }
    public static function ActionPort($domain, $Item = [])
    {

        $checkKeys = checkKeys::getObjectByItem($Item);
        
        foreach( $checkKeys->checkKeysRule->toArray() AS $checkKeysRule) {
            if (!empty($checkKeysRule) && empty($checkKeysRule->offshelf)) {
               
                $AccountGroups = Tools::fix_array_Key(checkKeys::getObjectByItem($Item)->checkKeysRule->toArray(), "AccountGroups");
                if ($AccountGroups != "Default") $AccountGroups = explode(",", join(",", $AccountGroups));                
                if (in_array($domain, $AccountGroups)) return  $checkKeys->ActionPort;
            }
        }
        


        if ($domain == DOMAIN_DEV)   return  "Admin";
        if ($domain == DOMAIN_CRON)   return  "LOCAL";
        if ($domain == DOMAIN_VUE)   return  "Clientele";
    }
    public static function checkKeys_init()
    {
        //使用原生models 刷新所有資料UniqueID

        $checkKeys = checkKeys::find();
        foreach ($checkKeys as $row) {
            $row->UniqueID = _UniqueID::insertUniqueID("checkKeys")['UniqueID'];
            $row->save();
        }
    }

    public static function RedirectAdmin_init()
    {

        $Insert = [];
        $Insert['ViewsType'] = "phtml";
        $Insert['ViewsPath'] = "Input";

        //增加 Input_Nav
        if (RedirectAdmin::count("ReDirect = 'Input_Nav' ") == 0) {
            $Insert['ReDirect'] = "Input_Nav";
            $Insert['FileName'] = "Input_Nav.phtml";
            Models::insertTable($Insert, "RedirectAdmin");
        }

        //增加 Input_ReDirect
        if (RedirectAdmin::count("ReDirect = 'Input_RedirectAdmin' ") == 0) {
            $Insert['ReDirect'] = "Input_RedirectAdmin";
            $Insert['FileName'] = "Input_RedirectAdmin.phtml";
            Models::insertTable($Insert, "RedirectAdmin");
        }

        //增加 Input_checkKeys管理頁面
        if (RedirectAdmin::count("ReDirect = 'Input_checkKeys' ") == 0) {
            $Insert['ReDirect'] = "Input_checkKeys";
            $Insert['FileName'] = "Input_checkKeys.phtml";
            Models::insertTable($Insert, "RedirectAdmin");
        }

        //增加 Input_checkKeys管理頁面
        if (RedirectAdmin::count("ReDirect = 'Input_checkKeysRule' ") == 0) {
            $Insert['ReDirect'] = "Input_checkKeysRule";
            $Insert['FileName'] = "Input_checkKeysRule.phtml";
            Models::insertTable($Insert, "RedirectAdmin");
        }

        $Insert['ViewsPath'] = "admin";
        //增加 sign-in
        if (RedirectAdmin::count("ReDirect = 'sign-in' ") == 0) {
            $Insert['ReDirect'] = "sign-in";
            $Insert['FileName'] = "sign-in.phtml";
            Models::insertTable($Insert, "RedirectAdmin");
        }
        //使用原生models 刷新所有資料UniqueID

        $RedirectAdmin = RedirectAdmin::find();
        foreach ($RedirectAdmin as $row) {
            $row->UniqueID = _UniqueID::insertUniqueID("RedirectAdmin")['UniqueID'];
            $row->save();
        }
    }
    public static function _checkKeys($Item)
    {
        $Return = [];
        $checkKeys = checkKeys::getListByItem($Item);
        foreach ($checkKeys as $row) {
            $checkKeys = explode(",", $row['PostData']);
            $Return[$row['ActionName']] = (!empty($checkKeys)) ? $checkKeys : [];
        }

        return $Return;
    }
    public static function checkKeys($checkKeys, $Item)
    {

        if (isset($checkKeys->UniqueID))
            $checkKeysRule = checkKeysRule::getObjectByItem(["UniqueID_checkKeys" => $checkKeys->UniqueID]);
        else return true;
        if (!empty($checkKeysRule->admin_disable) && $_SERVER['SERVER_NAME'] != DOMAIN_DEV) {
            $Return['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
            $Return['ErrorMsg'][] = " SERVER_NAME denied ";
            $Return['Msg'] = "FAILD";
            $Return['Domain'] = $_SERVER['HTTP_HOST'];
            echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
            exit;
        }


        if (empty($checkKeys->ControllerName) || $checkKeys->ControllerName != $Item['ControllerName']) {
            $Return['ControllerName'] = $checkKeys->ControllerName;
            $Return['ErrorMsg'][] = " ControllerName denied ";
            $Return['Msg'] = "FAILD";
            $Return['Domain'] = $_SERVER['HTTP_HOST'];
            $Return['Item'] = $Item;
            echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($checkKeys->ActionName != $Item['ActionName']) {
            $Return['ActionName'] = $checkKeys->ActionName;
            $Return['Item'] = $Item;
            $Return['ErrorMsg'][] = "ActionName denied";
            $Return['Msg'] = "FAILD";
            echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (empty($checkKeys->UniqueID)) {
            $Return['ErrorMsg'][] = "permission denied";
            $Return['Msg'] = "FAILD";
            echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
