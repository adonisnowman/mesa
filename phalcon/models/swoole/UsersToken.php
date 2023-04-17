<?php


class UsersToken extends BaseModel
{
      public static $tableName = __CLASS__;
      public function initialize()
      {
            $this->setConnectionService('swoole');
            $this->setSource(self::$tableName);
            $this->hasOne(
                  'UniqueID_Users',
                  Users::class,
                  'UniqueID',
                  []
            );
            $this->hasOne(
                  'UniqueID_UsersLoginLogs',
                  UsersLoginLogs::class,
                  'UniqueID',
                  []
            );
      }


      public function beforeValidationOnCreate()
      {
            $this->created_time = Tools::getDateTime();


            $TokenId =  $this->password_confirm . $this->UniqueID_Users . $this->UniqueID_UsersLoginLogs;

            if (empty($this->user_token)) $this->user_token = Tools::getToken($TokenId);
      }

      public function beforeValidationOnUpdate()
      {
            $this->updated_time = Tools::getDateTime();

            if (empty($this->faild_time)) {
                  $TokenId =  $this->password_confirm . $this->UniqueID_Users . $this->UniqueID_UsersLoginLogs;
                  $this->user_token = Tools::getToken($TokenId);
                  $this->token_refresh_time = Tools::getDateTime();
            }
      }

      public function beforeSave()
      {
            $Item['UniqueID'] = $this->UniqueID;
            if (!empty(self::getObjectById($Item))) $this->updated_time = Tools::getDateTime();
            else  $this->created_time = Tools::getDateTime();

            if (!empty(self::getObjectById($Item)) && empty($this->faild_time)) {
                  $TokenId =  $this->password_confirm . $this->UniqueID_Users . $this->UniqueID_UsersLoginLogs;
                  $this->user_token = Tools::getToken($TokenId);
                  $this->token_refresh_time = Tools::getDateTime();
            }
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
