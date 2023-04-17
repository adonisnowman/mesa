<?php


class MessageDataFile extends BaseModel
{
    public static $tableName = __CLASS__;
    public function initialize()
    {
        $this->setConnectionService('swoole');
        $this->setSource(self::$tableName);
        $this->hasOne(
            'UniqueID_UserTempMessages',
            UserTempMessages::class,
            'UniqueID',
            []
        );
        $this->hasOne(
            'UniqueID_UserMessageSchedule',
            UserMessageSchedule::class,
            'UniqueID',
            []
        );
        
    }


    public function beforeValidationOnCreate()
    {
        $this->created_time = Tools::getDateTime();
       
    }

    public function beforeValidationOnUpdate()
    {
       
    }

    public function beforeSave()
    {
      if (!empty($this->encrypted_data) && !empty($this->UniqueID)) {
            $this->encrypted_data = Json_encode($this->encrypted_data );
            $this->encrypted_data = Tools::Crypt($this->encrypted_data,false,$this->UniqueID);
      }
    }

    public function afterFetch()
    {
      if (!empty($this->encrypted_data) && !empty($this->UniqueID)) {
            $this->encrypted_data = Tools::Crypt($this->encrypted_data,true,$this->UniqueID);
            $this->encrypted_data = Json_decode($this->encrypted_data , true);
      }
    }

    public function afterSave()
    {
       
    }

    public static function getObjectById($Item, $SqlAnd = false)
      {
            $keys = ["UniqueID"];
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys).(($SqlAnd)?" AND ({$SqlAnd}) ":""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneById($Item, $SqlAnd = false)
      {

            $keys = ["UniqueID"];
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys).(($SqlAnd)?" AND ({$SqlAnd}) ":""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item)) ? [] : $Item->toArray();
      }
      public static function getObjectByItem($Item, $SqlAnd = false)
      {
            $keys = array_keys($Item);
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys).(($SqlAnd)?" AND ({$SqlAnd}) ":""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneByItem($Item, $SqlAnd = false)
      {

            $keys = array_keys($Item);
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys).(($SqlAnd)?" AND ({$SqlAnd}) ":""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item->UniqueID)) ? [] : $Item->toArray();
      }

      public static function getListByItem($Item, $SqlAnd = false)
      {

            $keys = array_keys($Item);
            $List = self::$tableName::find([
                  'conditions' => Models::Conditions($keys).(($SqlAnd)?" AND ({$SqlAnd}) ":""),
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
                  'conditions' => Models::Conditions($keys).(($SqlAnd)?" AND ({$SqlAnd}) ":""),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return $Object;
      }
}
