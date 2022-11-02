<?php

class _UsersApi
{

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

    public static function UserFootPoint($Ojbects)
    {
        if (empty($Ojbects)) $Return = [];
        else {
            foreach ($Ojbects as $Object) {
                $Item = $Object->toArray();
                $Return[] = $Item;
            }
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
