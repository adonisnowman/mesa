<?php

class MongoAdonis
{


	protected static $Manager;

	public function __construct($mongoDB)
	{

		self::$Manager = $mongoDB;
	}

	public static function collstats($databaseName)
	{
		$cursor = self::$Manager->executeCommand($databaseName, new MongoDB\Driver\Command(['listCollections' => 1, "authorizedCollections" => true]));
		$response = $cursor->toArray();
		return $response;
	}
	public static function drop($databaseName, $collectionName)
	{
		return self::$Manager->executeCommand($databaseName, new \MongoDB\Driver\Command(["drop" => $collectionName]));
	}
	public static function find($Query = "adonis", $options = [])
	{

		if (is_array($Query) && isset($Query[0])) $Concern = $Query[0];
		else $Concern = $Query;

		$filter = (isset($Query['filter'])) ? $Query['filter'] : array();


		$query = new MongoDB\Driver\Query($filter, $options);
		$readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
		$cursor = self::$Manager->executeQuery($Concern, $query, $readPreference);



		return self::toArray($cursor);
	}

	public static function insert($Query = "adonis", $options = [])
	{

		if (is_array($Query) && isset($Query[0])) $Concern = $Query[0];
		else $Concern = $Query;

		if (empty($options['_id']))
			$options['_id'] = new MongoDB\BSON\ObjectID;

		if (!isset($options['createTime'])) $options['createTime'] = Tools::getDateTime();

		$bulk = new MongoDB\Driver\BulkWrite();


		$bulk->insert($options);

		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result = self::$Manager->executeBulkWrite($Concern, $bulk, $writeConcern);


		return ["ResultObject" => $result, "mongoId" => (string)new MongoDB\BSON\ObjectID($options['_id'])];
	}

	public static function update($Query = "adonis", $options = [])
	{

		if (is_array($Query) && isset($Query[0])) $Concern = $Query[0];
		else $Concern = $Query;

		$filter = (isset($Query['filter'])) ? $Query['filter'] : array();


		$bulk = new MongoDB\Driver\BulkWrite();

		if (isset($options['_id'])) unset($options['_id']);

		if (!isset($options['updateTime'])) $options['updateTime'] = Tools::getDateTime();

		$bulk->update($filter, ['$set' => $options], ['multi' => false, 'upsert' => false]);

		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
		$result = self::$Manager->executeBulkWrite($Concern, $bulk, $writeConcern);


		return $result;
	}

	public static function delete($Query = "adonis", $options = [])
	{

		if (is_array($Query) && isset($Query[0])) $Concern = $Query[0];
		else $Concern = $Query;

		$filter = (isset($Query['filter'])) ? $Query['filter'] : array();


		$bulk = new MongoDB\Driver\BulkWrite();


		$bulk->delete($filter,  $options);

		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result = self::$Manager->executeBulkWrite($Concern, $bulk, $writeConcern);


		return $result;
	}

	public static function toArray($obj)
	{



		$reutrn = false;

		foreach ($obj as $document) {
			// 				var_dump($document,'---------------');

			$data = [];

			if (is_object($document)) {
				foreach ($document as $key => $item) {
					$data[$key] = self::respectTypeMap($item);
				}
			} else continue;

			$reutrn[] = $data;
		}

		return $reutrn;
	}


	public static function respectTypeMap($responseValue)
	{
		$returnValue = $responseValue;
		if ($responseValue instanceof \stdClass) {
			$returnValue = (array)$responseValue;
		}

		if (is_array($returnValue)) {
			foreach ($returnValue as &$value) {
				$value = self::respectTypeMap($value);
			}
		}

		return $returnValue;
	}
}
