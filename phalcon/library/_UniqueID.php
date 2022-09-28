<?php

class _UniqueID
{

    private static $Encryptionkey = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz1234567890";
    

    function __construct()
    {
        
    }
    public function shortUniqueID(){
        //加入隨機資料ID
        $Encryptionkey = self::$Encryptionkey;
        $microtime =(int)  round(((float)microtime(true)));
        $shortCode = [];
        while($microtime > 0){
            $num = $Encryptionkey[$microtime % strlen($Encryptionkey)];
            $microtime = floor($microtime / strlen($Encryptionkey));
            $shortCode[] = $num;
        }
        
        return join("",$shortCode);
    }
    public static function checkEmptyData(string $tableName, array $Item)
    {

        $Item = $tableName::getOneByItem($Item);
        return (empty($Item));
    }

    public static function EmptyTable(string $tableName)
    {

        $UniqueIDsLog = UniqueIDsLog::find([
            'conditions' => " table_name = :table_name: ",
            'bind'       => ["table_name" => $tableName],
        ]);

        $UniqueIDsLog->delete();


        $ObjectList = $tableName::find();
        $ObjectList->delete();
    }



    //-----
    public static function loadUniqueID(string $ID, $tableName = false, $field = false)
    {

        $Item = [];
        $Return = [];
        $Item['UniqueID'] = self::formatUniqueID($ID)["UniqueID"];
        $Item['header'] = self::formatUniqueID($ID)["header"];
        if (empty($Item['header'])) unset($Item['header']);

        if (!empty($tableName)) {
            $Item['table_name'] = $tableName;
        } else {

            $UniqueIDsLog = UniqueIDsLog::getOneById($Item);
            if (!empty($UniqueIDsLog['table_name'])) {
                $Item['table_name'] = $UniqueIDsLog['table_name'];
            } else {
                $Item['data'] = ["errorMsg" => "table is null"];
                return $Item;
            }
        }

        $Item['Object'] = $Item['table_name']::getObjectById($Item);
        $Item['data'] = (!empty($field)) ? Tools::fix_element_Key($Item['table_name']::getOneById($Item), $field) : $Item['table_name']::getOneById($Item);
        
        return $Item;
    }

    public static function LoadUniqueIDObject(string $ID, $tableName = false, $field = false)
    {

        $Item = [];
        $Return = [];
        $Item['UniqueID'] = self::formatUniqueID($ID)["UniqueID"];
        $Item['header'] = self::formatUniqueID($ID)["header"];
        if (empty($Item['header'])) unset($Item['header']);

        if (!empty($tableName)) {
            $Item['table_name'] = $tableName;
        } else {

            $UniqueIDsLog = UniqueIDsLog::getObjectById($Item);
            if (!empty($UniqueIDsLog->table_name)) {
                $Item['table_name'] = $UniqueIDsLog->table_name;
            } else {
                $Item['data'] = ["errorMsg" => "table is null"];
                return $Item;
            }
        }

        $Item['Object'] = $Item['table_name']::getObjectById($Item);
        $Item['UniqueID'] = $UniqueIDsLog;
        
        return $Item;
    }


    public static function usedUniqueID(array $UniqueIDItem)
    {

        $UniqueIDItem = Tools::fix_element_Key($UniqueIDItem, ["UniqueID", "header", "table_name"]);
        $UniqueIDsLog = UniqueIDsLog::getObjectById($UniqueIDItem);
        $UniqueIDsLog->lastused_time = Tools::getDateTime();
        $UniqueIDsLog->save();
    }


    public static function updateUniqueID(array $UniqueIDItem)
    {
        $UniqueIDItem = Tools::fix_element_Key($UniqueIDItem, ["UniqueID", "header", "table_name"]);
        $UniqueIDsLog = UniqueIDsLog::getObjectById($UniqueIDItem);
        $UniqueIDsLog->updated_time = Tools::getDateTime();
        $UniqueIDsLog->save();
    }



    public static function insertUniqueID(string $tableName)
    {
        
        if (class_exists($tableName) == false) {
            $Return['ErrorMsg'][] =  "table is not find";
            echo json_encode($Return, JSON_UNESCAPED_UNICODE);
            exit();
        }
       




        $ID = self::getUniqueID();

        $Insert = [];
        $Insert['UniqueID'] = self::formatUniqueID($ID)["UniqueID"];
        $Insert['header'] = self::formatUniqueID($ID)["header"];
        $Insert['table_name'] = $tableName;



        $keys = array_keys($Insert);
        $UniqueIDsLog = UniqueIDsLog::findFirst([
            'conditions' => Models::Conditions($keys),
            'bind'       => Tools::fix_element_Key($Insert, $keys),
        ]);

        if (empty($UniqueIDsLog->UniqueID)) {
            //建立唯一碼專用 無法使用Models 新增 請注意
            $UniqueIDsLog = new UniqueIDsLog();
            $UniqueIDsLog->assign($Insert);
            $UniqueIDsLog->create();
            $messages = $UniqueIDsLog->getMessages();

            foreach ($messages as $message) {
                echo $message;
            }
            return $UniqueIDsLog->toArray();
        } else {
            return self::insertUniqueID($tableName);
        }
    }




    public static function getUniqueID()
    {
        //加入隨機資料

        $microtimeString =  (string) round(((float)microtime(true) - (float)time()) * pow(10, 6));
        $Encryptionkey = Encryptionkey::getObjectById(["timestamp" => $microtimeString]);

        if(!empty($Encryptionkey->timestamp))  
            $regex = str_split($Encryptionkey->Encryptionkey);
        else {
            $regex = str_split(self::$Encryptionkey);
            shuffle($regex) ;            
        }
        
        $key = str_split("yjdHis");
        shuffle($key);
        $time = [];
        
        foreach ($key as $k => $char) {
            $microtime = (string) (isset($microtimeString[$k])) ? $microtimeString[$k] : "0";
            $time[$char] = $regex[intval(date($char))] . $microtime;
        }
        $randomCheck = $key[round(rand(0, count($key) - 1))];
        $time['yearheader'] = (string) ((int) date($randomCheck))  * pow(10, 6) - $microtimeString;

        $Return = join("", $time) . "_{$microtimeString}";    
        
        //寫入新資料
        if(empty($Encryptionkey->timestamp)) {
            $Encryptionkey = new Encryptionkey;
            $Encryptionkey->timestamp = $microtimeString;
            $Encryptionkey->Encryptionkey = join("",$regex);            
            $Encryptionkey->create();
        }
        

        return (self::checkUniqueID($Return,join("",$regex))) ? $Return : self::getUniqueID();
    }

    public static function checkUniqueID(string $ID ,$Encryptionkey = false)
    {
        
        if (strlen($ID) > 18 && strlen($ID) < 20 && false == strpos('-', $ID) && 0 == preg_match("/[0-9a-zA-Z]{12}[0-9]{6,8}/",$ID)) return false;
       
        if(!empty($Encryptionkey)){
            $Encryptionkey_new = $Encryptionkey;
        }else {
            
            $UniqueIDsLog = UniqueIDsLog::getObjectById(["UniqueID" => self::formatUniqueID($ID)["UniqueID"]]);
       
            if(!empty($UniqueIDsLog->header)) {
                $Encryptionkey = Encryptionkey::getObjectById(["timestamp"=>$UniqueIDsLog->header]);
                $Encryptionkey_new = $Encryptionkey->Encryptionkey;
                $Encryptionkey->used_time = Tools::getDateTime();
                $Encryptionkey->save();                
            }
        }
        
        //解碼判斷
        $UniqueID = self::formatUniqueID($ID)["UniqueID"];
        $microtime = (int)(substr($UniqueID, 12, strlen($UniqueID)) + (int) ($ID[1] . $ID[3] . $ID[5] . $ID[7] . $ID[9] . $ID[11])) / pow(10, 6);
        $checkCount = $microtime - floor($microtime);
        if(empty($Encryptionkey_new)) return false;
        else $regex = str_split($Encryptionkey_new);
        if(!isset($regex[$microtime])) return false;
        else $regixCheck = $regex[$microtime];
       
        return ($checkCount == 0 && in_array($regixCheck, [$ID[0], $ID[2], $ID[4], $ID[6], $ID[8], $ID[10]]))?$ID:false;
    }

    public static function formatUniqueID(string $ID)
    {
        $ID = explode("_",$ID);
        $Return["UniqueID"] =  $ID[0];
        $Return["header"] =  isset($ID[1])?$ID[1]:"";
        return $Return;
    }

    public static function UniqueIDItemToString(array $UniqueIDItem)
    {
        return $UniqueIDItem['UniqueID'] . $UniqueIDItem['header'];
    }

    public static function privateUniqueID(string $publicUniqueID)
    {
        if (false == self::checkPublicUniqueID($publicUniqueID)) return false;
        $UniqueID = self::formatUniqueID($publicUniqueID)['UniqueID'];
        $keys = [1, 3, 5, 7, 9, 11];

        foreach (str_split($UniqueID) as $k => $char) {
            $privateUniqueID[] = ($k >= 11 || in_array($k, $keys)) ? strpos(self::$Encryptionkey, (string) $char) : $char;
        }

        return join("", $privateUniqueID);
    }

    public static function publicUniqueID(string $privateUniqueID)
    {
        if (false == self::checkUniqueID($privateUniqueID)) return false;
        $UniqueID = self::formatUniqueID($privateUniqueID)['UniqueID'];
        $regex = "/([1-9a-zA-Z][0-9]){6}[0-9]+/";
        $keys = [1, 3, 5, 7, 9, 11];
        if (preg_match($regex, $UniqueID)) 
            foreach (str_split($UniqueID) as $k => $char) {
                $publicUnique[] = (is_numeric($char) && ($k >= 11 || in_array($k, $keys))) ? str_split(self::$Encryptionkey)[(int)$char] : $char;
            }
        else return false;

        return join("", $publicUnique);
    }

    public static function checkPublicUniqueID(string $publicUnique)
    {
        $regex = "/([1-9a-zA-Z][a-eA-E]){6}[a-eA-E]+/";
        return preg_match($regex, $publicUnique);
    }

    
}
