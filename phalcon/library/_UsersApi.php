<?php

class _UsersApi
{

    

    //讀取資料格式

    public static function Users($Ojbects){
        if(empty($Ojbects)) $Return = [];
        else {
            foreach($Ojbects AS $Object){
                $Item = $Object->toArray();
                $Item['offshelf'] = (!empty($Item['offshelf']));


                $Item['videos'] = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/video/".$Item['UniqueID'],"[a-zA-Z0-9]+\.gif");
                $Item['Images'] = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/ImagesDir/".$Item['UniqueID'],"[a-zA-Z0-9]+\.jpg");
                if(!empty($Item['videos']))
                foreach($Item['videos'] AS &$video){
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

