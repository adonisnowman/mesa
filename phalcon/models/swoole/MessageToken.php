<?php


class MessageToken extends BaseModel
{
      public static $tableName = __CLASS__;
      public function initialize()
      {
            $this->setConnectionService('swoole');
            $this->setSource(self::$tableName);
            $this->hasOne(
                  'UniqueID_MessageType',
                  MessageType::class,
                  'UniqueID',
                  []
            );
      }


      public function beforeValidationOnCreate()
      {
            if( empty($this->MessageType) || ( !empty($this->MessageType->start_time) && strtotime($this->MessageType->start_time) > time() )  ){
                  echo "訊息類型尚未啟用，無法新增該項資料";
                  exit;
            }
            if( !empty($this->MessageType) && empty($this->MessageType->UserMessageTypeDefaultCosts) ){
                  echo "查無訊息類型收費標準，無法新增該項資料";
                  exit;
            }
            $UserMessageTypeDefaultCosts = $this->MessageType->UserMessageTypeDefaultCosts;
            $this->created_time = Tools::getDateTime();
            $UserMessageTypeDefaultCosts = Tools::fix_element_Key( $UserMessageTypeDefaultCosts->toArray() , ["action_offshelf","used_offshelf","action_cost","used_cost"]);

            $TokenId =  $this->UniqueID_MessageType . $this->UniqueID_UsersLoginLogs . $this->shortUniqueID_SwooleConnections;

           if(empty( $this->action_token)) $this->action_token = Tools::getToken(  $TokenId , " +30 minutes " , $UserMessageTypeDefaultCosts );

      }

      public function beforeValidationOnUpdate()
      {
            $this->updated_time = Tools::getDateTime();
      }

      public function beforeSave()
      {
            $Item['UniqueID'] = $this->UniqueID;
            if (!empty(self::getObjectById($Item))) $this->updated_time = Tools::getDateTime();
            else  $this->created_time = Tools::getDateTime();
      }

      public function afterFetch()
      {

           
      }

      public function afterSave()
      {
            
      }

      public static function getObjectById($Item, $SqlAnd = false)
      {
            $keys = ["UniqueID"];
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys) . (($SqlAnd) ? " AND ({$SqlAnd}) " : ""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneById($Item, $SqlAnd = false)
      {

            $keys = ["UniqueID"];
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys) . (($SqlAnd) ? " AND ({$SqlAnd}) " : ""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item)) ? [] : $Item->toArray();
      }
      public static function getObjectByItem($Item, $SqlAnd = false)
      {
            $keys = array_keys($Item);
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys) . (($SqlAnd) ? " AND ({$SqlAnd}) " : ""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneByItem($Item, $SqlAnd = false)
      {

            $keys = array_keys($Item);
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys) . (($SqlAnd) ? " AND ({$SqlAnd}) " : ""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item->UniqueID)) ? [] : $Item->toArray();
      }

      public static function getListByItem($Item, $SqlAnd = false)
      {

            $keys = array_keys($Item);
            $List = self::$tableName::find([
                  'conditions' => Models::Conditions($keys) . (($SqlAnd) ? " AND ({$SqlAnd}) " : ""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            $List = (empty($List)) ? [] : $List->toArray();

            return $List;
      }

      public static function getListObjectByItem($Item, $SqlAnd = false)
      {

            $keys = array_keys($Item);
            $Object = self::$tableName::find([
                  'conditions' => Models::Conditions($keys) . (($SqlAnd) ? " AND ({$SqlAnd}) " : ""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return $Object;
      }
}
