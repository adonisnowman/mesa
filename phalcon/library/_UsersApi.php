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


                $files = Tools::getFileList("/home/cfd888/public_html/swoole.bestaup.com/video","[a-zA-Z0-9]+\.gif");
                var_dump($files);


                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    
}
