<?php


class OneDalla extends BaseModel
{

	public static $tableName = __CLASS__;
	public function initialize()
	{		
        $this->setConnectionService('DIYA_DATA');
        $this->setSource(self::$tableName);
	}

	public function beforeValidationOnCreate()
	{
        preg_match_all("/(?P<num>[0-9]+)/",$this->Amount,$matchs);
       
		$this->Amount = (int) join("",$matchs['num']);

        preg_match("/(?P<status>(交易成功|交易失敗))/",$this->status,$matchs);
        $this->status = $matchs['status'];
        $this->Bank_account = str_pad( $this->Bank_account,16,'0',STR_PAD_LEFT);
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
