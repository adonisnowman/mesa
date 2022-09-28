<?php


class checkKeysRule extends BaseModel
{
    public static $tableName = __CLASS__;
	public function initialize()
	{
		
        $this->setSource( self::$tableName);
        $this->hasOne(
            'UniqueID_checkKeys',
            checkKeys::class,
            'UniqueID',[]
        );
        $this->hasOne(
            'UniqueID_Accounts',
            Accounts::class,
            'UniqueID',[]
        );
    }

    public function beforeValidationOnCreate()
    {
        $this->created_time = Tools::getDateTime();
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
