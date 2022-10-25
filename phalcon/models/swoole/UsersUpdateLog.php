<?php


class UsersUpdateLog extends BaseModel
{

	public static $tableName = __CLASS__;
	public function initialize()
	{		
        $this->setConnectionService('swoole');
        $this->setSource(self::$tableName);
        $this->hasOne(
            'UniqueID_Users',
            Users::class,
            'UniqueID',[]
        );
        $this->hasOne(
            'UniqueID_UsersConnection',
            UsersConnection::class,
            'UniqueID',[]
        );
        $this->hasOne(
            'UniqueID_EmailChecked',
            EmailChecked::class,
            'UniqueID',[]
        );
        $this->hasOne(
            'UniqueID_MobileChecked',
            MobileChecked::class,
            'UniqueID',[]
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
		
        
	}

	public function afterFetch()
	{
		
        
		
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
