<?php

class Models
{
    public static $DefultSelect = ["","order"=>"id DESC"];
    
    public static function SelectLike($SelectKeys,$Search){
            foreach($SelectKeys AS &$Item) 
                $Item = $Item . " like '%".$Search."%' ";
            
            return join(" OR ",$SelectKeys);
    }
    public static function Conditions($Keys, $joinStr = " AND ")
    {

        return join($joinStr, array_map(function ($value) {
            return " {$value} = :{$value}: ";
        }, $Keys));
    }

    public static function getMongoID($mongo_id)
    {
        return new MongoDB\BSON\ObjectID($mongo_id);
    }

    public static function DefultSelect($Pages,$Select="",$entriesLimit = 20){
        $Return =  self::$DefultSelect;
        $Return["limit"] = $entriesLimit;
        $Return["offset"] = $Pages['Offset'];
        $Return[0] = $Select;
        return $Return;
    }
    public static function insertTable($Insert,$table_name,$object = false)
    {
        $Return['Data']['action'] = "Insert";
        $UniqueID = _UniqueID::insertUniqueID($table_name);
        if(empty($UniqueID['UniqueID'])) $Return['ErrorMsg'][] = "Server Busy , please waiting";
        else {

            $Insert['UniqueID'] = $UniqueID["UniqueID"];        
        
            $UniqueID['table_name'] = new $UniqueID['table_name'];
            $UniqueID['table_name']->assign($Insert);
            $UniqueID['table_name']->create();

            foreach ($UniqueID['table_name']->getMessages() as $message) {
                $Return['SystemMsg'][] = $message;
            }

            if($object) return $UniqueID['table_name'];
            $Return['Object'] = $UniqueID['table_name'];
            if(empty($Return['SystemMsg'])) $Return['Data'][$table_name] = $UniqueID['table_name']->toArray();
        }

        return $Return;
    }
}
