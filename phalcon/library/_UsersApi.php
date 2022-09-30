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
                $Return[] = $Item;
            }            
        }
        return $Return;
    }

    
}
