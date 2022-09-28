<?php

class RedirectAdmin extends BaseModel
{

	public static $tableName = __CLASS__;
	public function initialize()
	{		
        self::$tableName = str_replace( __NAMESPACE__."/","",self::$tableName);
        $this->setSource(self::$tableName);
        $this->hasOne(
            'ReDirect',
            checkKeys::class,
            'ActionName',[]
        );
	}
    
    public static function CheckFileByPhtml($Object){
        if(empty($Object->ViewsType)) $Object->ViewsType = "phtml";
        if($Object->ViewsType == "phtml" && !empty($Object->FileName)){
            $FileDir = './phalcon/views/';    
            if(is_file($FileDir.$Object->ViewsPath."/".$Object->FileName))
            return  Tools::getDateTime();
        }
        return Null;
    }



	public function beforeValidationOnCreate()
	{

		$this->created_time = Tools::getDateTime();
		$this->checked_time = self::CheckFileByPhtml($this);		
	}

	public function beforeValidationOnUpdate()
	{
		$this->updated_time = Tools::getDateTime();
		$this->checked_time = self::CheckFileByPhtml($this);
	}

	public function beforeSave()
	{
		$this->updated_time = Tools::getDateTime();
		$this->checked_time = self::CheckFileByPhtml($this);
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
