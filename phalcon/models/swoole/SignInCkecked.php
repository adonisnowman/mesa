<?php


class SignInCkecked extends BaseModel
{
      public static $tableName = __CLASS__;
      public function initialize()
      {
            $this->setConnectionService('swoole');
            $this->setSource(self::$tableName);
            $this->hasOne(
                  'UniqueID_SignInList',
                  SignInList::class,
                  'UniqueID',
                  []
            );
            $this->hasOne(
                  'UniqueID_EmailChecked',
                  EmailChecked::class,
                  'UniqueID',
                  []
            );
            $this->hasOne(
                  'UniqueID_MobileChecked',
                  MobileChecked::class,
                  'UniqueID',
                  []
            );
      }

      public function beforeValidationOnCreate()
      {
      }

      public function beforeValidationOnUpdate()
      {
      }
      public function afterFetch()
      {
      }

      public function beforeSave()
      {
      }
      public function afterSave()
      {
            self::SetInSession($this);
      }

      public function beforeUpdate()
      {
      }
      public function afterCreate()
      {
            self::SetInSession($this);
      }
      public function afterUpdate()
      {
            self::SetInSession($this);
      }
      public function beforeDelete()
      {
      }

      public static function SetInSession($SignInCkecked)
      {
            
            if (!empty($SignInCkecked->email_checked_time))
                  $_SESSION[Tools::getIp()]['SignInSession']['SignInCkecked']['email_checked_time']  = $SignInCkecked->email_checked_time;
            if (!empty($SignInCkecked->mobile_checked_time))
                  $_SESSION[Tools::getIp()]['SignInSession']['SignInCkecked']['mobile_checked_time']  = $SignInCkecked->mobile_checked_time;
            if (!empty($SignInCkecked->password_checked_time))
                  $_SESSION[Tools::getIp()]['SignInSession']['SignInCkecked']['password_checked_time']  = $SignInCkecked->password_checked_time;
            if (!empty($SignInCkecked->password_confirm))
                  $_SESSION[Tools::getIp()]['SignInSession']['SignInCkecked']['password_confirm']  = $SignInCkecked->password_confirm;
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
