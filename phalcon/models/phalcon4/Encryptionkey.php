<?php


class Encryptionkey extends BaseModel
{    
    public static $tableName = __CLASS__;
	public function initialize()
	{		
        $this->setSource(self::$tableName);
        $this->hasMany(
            'timestamp',
            UniqueIDsLog::class,
            'header',[]
        );
    }

    public function beforeValidationOnCreate()
    {
        $this->created_time = Tools::getDateTime();
    }
    public function beforeValidationOnUpdate(){
        $this->updated_time = Tools::getDateTime();
    }
    public function beforeSave(){
        if (!empty($this->Encryptionkey)) {
			$this->Encryptionkey = Tools::Crypt($this->Encryptionkey, false , $this->timestamp);
		}
    }
    public function afterFetch(){
        if (!empty($this->Encryptionkey)) {
			$this->Encryptionkey = Tools::Crypt($this->Encryptionkey, true , $this->timestamp);
		}

		return $this;
    }
    public function afterSave(){}

    public static function getObjectById($Item)
    {
        $keys = ["timestamp"];
        $Object = self::$tableName::findFirst([
            'conditions' => Models::Conditions($keys),
            'bind'       => Tools::fix_element_Key($Item, $keys),
            'for_update' => true,
        ]);
        return $Object;
    }

    public static function getOneById($Item)
    {

        $keys = ["timestamp"];
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
