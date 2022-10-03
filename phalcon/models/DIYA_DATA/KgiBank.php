<?php


class KgiBank extends BaseModel
{

      public static $tableName = __CLASS__;
      public function initialize()
      {
            $this->setConnectionService('DIYA_DATA');
            $this->setSource(self::$tableName);
      }

      public function beforeValidationOnCreate()
      {
            preg_match_all("/(?P<num>[0-9]+)/", $this->payout, $matchs);
            $this->payout = (int) join("", $matchs['num']);

            preg_match_all("/(?P<num>[0-9]+)/", $this->deposits, $matchs);
            $this->deposits = (int) join("", $matchs['num']);

            preg_match_all("/(?P<num>[0-9]+)/", $this->balance, $matchs);
            $this->balance = (int) join("", $matchs['num']);

            $this->Bank_account = str_pad( $this->Bank_account,16,'0',STR_PAD_LEFT);
            $this->created_time = date("Y-m-d H:i:s", strtotime($this->created_time));
            $this->account_date = date("Y-m-d", strtotime($this->account_date));
      }

      public function beforeValidationOnUpdate()
      {
      }

      public function beforeSave()
      {
      }

      public function afterFetch()
      {

            return $this;
      }

      public function afterSave()
      {
      }


      public static function resetValues($Item)
      {
            preg_match_all("/(?P<num>[0-9]+)/", $Item['payout'], $matchs);
            $Item['payout'] = (int) join("", $matchs['num']);

            preg_match_all("/(?P<num>[0-9]+)/", $Item['deposits'], $matchs);
            $Item['deposits'] = (int) join("", $matchs['num']);

            preg_match_all("/(?P<num>[0-9]+)/", $Item['balance'], $matchs);
            $Item['balance'] = (int) join("", $matchs['num']);

            
            $Item['created_time'] = date("Y-m-d H:i:s", strtotime($Item['created_time']));
            $Item['account_date'] = date("Y-m-d", strtotime($Item['account_date']));
            $Item['Bank_account'] = str_pad(  $Item['Bank_account'],16,'0',STR_PAD_LEFT);
            return $Item;
      }
      public static function getObjectById($Item)
      {
            $keys = ["UniqueID"];
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneById($Item)
      {

            $keys = ["UniqueID"];
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item)) ? [] : $Item->toArray();
      }
      public static function getObjectByItem($Item)
      {
            $keys = array_keys($Item);
            $Object = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);
            return $Object;
      }
      public static function getOneByItem($Item)
      {

            $keys = array_keys($Item);
            $Item = self::$tableName::findFirst([
                  'conditions' => Models::Conditions($keys),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            return (empty($Item->UniqueID)) ? [] : $Item->toArray();
      }

      public static function getListByItem($Item)
      {

            $keys = array_keys($Item);
            $List = self::$tableName::find([
                  'conditions' => Models::Conditions($keys),
                  'bind'       => Tools::fix_element_Key($Item, $keys),
                  'for_update' => true,
            ]);

            $List = (empty($List)) ? [] : $List->toArray();

            return $List;
      }
}
