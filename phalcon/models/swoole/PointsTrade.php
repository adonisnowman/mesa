<?php


class PointsTrade extends BaseModel
{
      public static $tableName = __CLASS__;
      public function initialize()
      {
            $this->setConnectionService('swoole');
            $this->setSource(self::$tableName);
            $this->hasOne(
                  'UniqueID_PointsOrder',
                  PointsOrder::class,
                  'UniqueID',[]
              );
              
             
      }

      public function beforeValidationOnCreate()
      {
            $this->created_time = Tools::getDateTime();
        $this->HTTP_HOST = $_SERVER['HTTP_HOST'];
        $this->REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
        $this->HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
            
      }

      public function beforeValidationOnUpdate()
      {
            $this->updated_time = Tools::getDateTime();
      }
      public function afterFetch()
      {

      }

      public function beforeSave()
      {
           
            
      }
      public function afterSave()
      {
            
      }

      public function beforeUpdate()
      {
           
      }
      public function beforeDelete()
      {
            
      }


      public static function getObjectById($Item, $SqlAnd = "")
      {
            $keys = ["UniqueID"];
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys)." AND ({$SqlAnd}) ",
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneById($Item, $SqlAnd = "")
      {

            $keys = ["UniqueID"];
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys)." AND ({$SqlAnd}) ",
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item)) ? [] : $Item->toArray();
      }
      public static function getObjectByItem($Item, $SqlAnd = "")
      {
            $keys = array_keys($Item);
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys)." AND ({$SqlAnd}) ",
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneByItem($Item, $SqlAnd = "")
      {

            $keys = array_keys($Item);
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys)." AND ({$SqlAnd}) ",
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item->UniqueID)) ? [] : $Item->toArray();
      }

      public static function getListByItem($Item, $SqlAnd = "")
      {

            $keys = array_keys($Item);
            $List = self::$tableName::find([
                  'conditions' => Models::Conditions($keys)." AND ({$SqlAnd}) ",
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            $List = (empty($List)) ? [] : $List->toArray();

            return $List;
      }

      public static function getListObjectByItem($Item, $SqlAnd = "")
      {

            $keys = array_keys($Item);
            $Object = self::$tableName::find([
                  'conditions' => Models::Conditions($keys)." AND ({$SqlAnd}) ",
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return $Object;
      }
}
