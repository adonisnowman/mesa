<?php


class UsersLoginLogs extends BaseModel
{
    public static $tableName = __CLASS__;
    public function initialize()
    {
        $this->setConnectionService('swoole');
        $this->setSource(self::$tableName);
        $this->hasOne(
            'UniqueID',
            Users::class,
            'UniqueID_UsersLoginLogs',
            []
        );
        $this->hasOne(
            'UniqueID_SignInList',
            SignInList::class,
            'UniqueID',
            []
        );

        $this->hasMany(
            'UniqueID',
            SwooleConnections::class,
            'UniqueID_UsersLoginLogs',
            []
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
