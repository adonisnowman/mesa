<?php

class _UsersApi
{
    public static function PasswordRecheck($Users, $Password)
    {
        if (!empty($Users->password) && $Password == $Users->password) {


            //更新密碼確認時間
            $SignInCkecked = $Users->SignInList->SignInCkecked;
            if (empty($SignInCkecked)) {
                $Return['ErrorMsg'][] = "身份驗證資料 異常，請聯絡客服";
                return $Return;
            }

            $SignInCkecked->password_confirm = Tools::getDateTime();
            $SignInCkecked->save();
            //建立UsersToken

            $Insert = [];
            $Insert['UniqueID_Users'] = $Users->UniqueID;

            //移除所有該會員已存在的尚未使用Token
            $UsersToken = UsersToken::getObjectByItem($Insert, " ( faild_time IS NULL AND  token_refresh_time IS NULL  ) ");

            if (!empty($UsersToken)) {
                  $UsersToken->faild_time = Tools::getDateTime();
                  $UsersToken->save();
            }

            $Insert['UniqueID_UsersLoginLogs'] = $_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'];
            $Insert['password_confirm'] = $Password;
            $UsersToken = UsersToken::getObjectByItem($Insert, " ( faild_time IS NULL AND  token_refresh_time IS NOT NULL  ) ");

            if(empty($UsersToken->UniqueID) ) $Return['UsersToken'] = Models::insertTable($Insert, "UsersToken", true);
            else  $UsersToken->save();
         
        } else if (empty($Users->password)) {
            $Return['ErrorMsg'][] = "您尚未建立註冊密碼";
        } else {
            $Return['ErrorMsg'][] = "密碼驗證失敗";
        }
        $Return['SignInCkecked'] = $_SESSION[Tools::getIp()]['SignInSession']['SignInCkecked'];

        return $Return;
    }
    public static function checkSignInCkecked($Password = false)
    {

        if (empty($_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'])) {
            $Return['ErrorMsg'][] = "請先登入";
            return $Return;
        }

        $UsersLoginLogs = (object) _UniqueID::LoadUniqueIDObject($_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'], "UsersLoginLogs")['Object'];
        $SignInList = $UsersLoginLogs->SignInList;
        $SignInCkecked = $SignInList->SignInCkecked;

        if (empty($SignInCkecked->EmailChecked))  $Return['NoticeMsg'][] = "電子信箱尚未認證完成";
        if (empty($SignInCkecked->MobileChecked)) $Return['NoticeMsg'][] = "手機號碼尚未認證完成";



        $EmailChecked = $SignInCkecked->EmailChecked;
        $MobileChecked = $SignInCkecked->MobileChecked;

        //已完成聯絡方式認證，尚未建立會員登入資料
        if (empty($UsersLoginLogs->Users) && !empty($SignInCkecked->email_checked_time) && !empty($SignInCkecked->mobile_checked_time)) {
            $Insert = [];
            $Insert['UniqueID_SignInList'] = $SignInList->UniqueID;
            $Insert['account'] = $SignInList->account;

            $Insert['mobile'] = $MobileChecked->mobile;
            $Insert['email'] = $EmailChecked->email;
            $Insert['password'] = Null;

            if (!empty($SignInList->password))
                $Insert['password'] = $SignInList->password;
            if (empty($SignInList->password) && !empty($Password))
                $Insert['password'] = $Password;


            if (!empty($Insert['password'])) {
                $SignInCkecked->password_checked_time = Tools::getDateTime();
                $SignInCkecked->save();
                $Insert['register_time'] = Tools::getDateTime();
            }

            $Users =  models::insertTable($Insert, "Users", true);
            if (!empty($Users->UniqueID))
                $Return['UsersObject'] = $Users;
        }

        return $Return;
    }
    public static function insertUsedMessagePoints($MessageDataFile, $action_cost)
    {
        $Insert = [];
        if (empty($MessageDataFile->UserTempMessages)) {
            $Return['ErrorMsg'][] = "查無暫存資料表相關資料";
        }
        $Insert['UniqueID_UserTempMessages'] = $MessageDataFile->UserTempMessages->UniqueID;
        $Insert['UniqueID_SignInList'] = $_SESSION[Tools::getIp()]['SignInList']['UniqueID'];
        $Insert['points_cost'] = $action_cost;
        $Insert['points_off'] = $action_cost;


        if (!empty($_SESSION[Tools::getIp()]['Users']))
            $Insert['UniqueID_Users'] = $_SESSION[Tools::getIp()]['Users']['UniqueID'];

        $UsedMessagePoints =  models::insertTable($Insert, "UsedMessagePoints", true);

        if (!empty($UsedMessagePoints->UniqueID)) {

            $Insert = [];
            $Insert['UniqueID_MessageDataFile'] = $MessageDataFile->UniqueID;
            $Insert['UniqueID_UsedMessagePoints'] = $UsedMessagePoints->UniqueID;
            $MessageSystemRecode = Models::insertTable($Insert, "MessageSystemRecode", true);

            $Return['MessageSystemRecode'] = $MessageSystemRecode->toArray();
            $Return['UsedMessagePoints'] = $UsedMessagePoints->toArray();
        } else {
            $Return['ErrorMsg'][] = "訊息點數消費紀錄 建立失敗";
        }

        return $Return;
    }
    public static function insertUserFootPoint($ClassName, $Value)
    {


        if ($ClassName == "IndexController") {
            if (is_string($Value)) $Item['ReDirect'] = $Value;
            if (!empty($Value['ReDirect'])) $Item['ReDirect'] = $Value['ReDirect'];

            $RedirectAdmin = RedirectAdmin::getOneByItem($Item);

            if (!empty($RedirectAdmin)) {
                $action_name = $Item['ReDirect'];
                $FootPoint = $RedirectAdmin['label'];
            }
        }

        if ($ClassName == "UsersApiController") {

            $Item['ControllerName'] = $ClassName;
            $Item['ActionName'] = $Value;
            $checkKeys = checkKeys::getOneByItem($Item);

            if (!empty($checkKeys)) {
                $action_name = $Item['ActionName'];
                $FootPoint = $checkKeys['label'];
            }
        }

        if (!empty($_SESSION[Tools::getIp()]['SignInList']))
            $Insert['UniqueID_SignInList'] = $_SESSION[Tools::getIp()]['SignInList']['UniqueID'];
        else $Insert['UniqueID_SignInList'] = Tools::getIp();


        if (!empty($_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs']))
            $Insert['UniqueID_UsersLoginLogs'] = $_SESSION[Tools::getIp()]['UniqueID_UsersLoginLogs'];
        else $Insert['UniqueID_UsersLoginLogs'] = Tools::getIp();

        $Insert['controller_name'] = $ClassName;
        $Insert['action_name'] = $action_name;
        $Insert['label'] = $FootPoint;

        return Models::insertTable($Insert, "UserFootPoint");
    }



    //讀取資料格式

    public static function SignInList($Ojbects)
    {
        $Return = [];
        if (empty($Ojbects)) return [];

        foreach ($Ojbects as $Object) {
            $Item = $Object->toArray();
            $Return[] = $Item;
        }

        return $Return;
    }

    public static function UserTempMessages($Ojbects)
    {
        $Return = [];
        if (empty($Ojbects)) return [];

        foreach ($Ojbects as $Object) {
            $Item = $Object->toArray();
            $Return[] = $Item;
        }

        return $Return;
    }

    public static function UserFootPoint($Ojbects)
    {
        $Return = [];
        if (empty($Ojbects)) return [];

        foreach ($Ojbects as $Object) {
            $Item = $Object->toArray();
            $Return[] = $Item;
        }

        return $Return;
    }

    public static function Users($Ojbects)
    {
        if (empty($Ojbects)) $Return = [];
        else {
            foreach ($Ojbects as $Object) {
                $Item = $Object->toArray();
                $Item['offshelf'] = (!empty($Item['offshelf']));


                $Item['Videos'] = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/video/" . $Item['UniqueID'], "[a-zA-Z0-9]+\.gif");
                $Item['Images'] = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/ImagesDir/" . $Item['UniqueID'], "[a-zA-Z0-9]+\.jpg");
                if (!empty($Item['Videos']))
                    foreach ($Item['Videos'] as &$video) {
                        $video = $Item['UniqueID'] . "/" . $video;
                    }
                if (!empty($Item['Images']))
                    foreach ($Item['Images'] as &$Images) {
                        $Images = $Item['UniqueID'] . "/" . $Images;
                    }

                $Return[] = $Item;
            }
        }
        return $Return;
    }
}
