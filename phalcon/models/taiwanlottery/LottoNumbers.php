<?php


class LottoNumbers extends BaseModel
{
      public static $tableName = __CLASS__;
      public function initialize()
      {
            
            $this->setConnectionService(basename(dirname(__FILE__)));
            $this->setSource(self::$tableName);
            $this->hasOne(
                  'UniqueID_SignInCkecked',
                  SignInCkecked::class,
                  'UniqueID',
                  []
            );
      }

      public function beforeValidationOnCreate()
      {
            $this->created_time = Tools::getDateTime();
            

            if (empty($this->offset) && !empty( $this->UniqueID)) $this->offset = 0;
           
      }

      public function beforeValidationOnUpdate()
      {
            $this->updated_time = Tools::getDateTime();
      }

      public function beforeSave()
      {
      }

      public function afterFetch()
      {
      }

      public function afterSave()
      {
            $this->updated_time = Tools::getDateTime();
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

      public static function getListByItem($Item = [], $SqlAnd = false)
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
