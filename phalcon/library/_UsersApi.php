<?php

class _UsersApi
{

    public static function UserFootPoint($ClassName,$Value){
        var_dump($ClassName);
        if($ClassName == "")
        exit;
    }

    //讀取資料格式

    public static function Users($Ojbects){
        if(empty($Ojbects)) $Return = [];
        else {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $Item['offshelf'] = (!empty($Item['offshelf']));


                $Item['Videos'] = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/video/".$Item['UniqueID'],"[a-zA-Z0-9]+\.gif");
                $Item['Images'] = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/ImagesDir/".$Item['UniqueID'],"[a-zA-Z0-9]+\.jpg");
                if(!empty($Item['Videos']))
                foreach($Item['Videos'] AS &$video){
                    $video = $Item['UniqueID']."/".$video;
                }
                if(!empty($Item['Images']))
                foreach($Item['Images'] AS &$Images){
                    $Images = $Item['UniqueID']."/".$Images;
                }

                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    
}

