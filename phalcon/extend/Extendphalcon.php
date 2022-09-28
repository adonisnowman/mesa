<?php

use \Phalcon\Mvc\Controller as PhController;
use \Phalcon\Mvc\Model as PhModel;
use \Phalcon\Mvc\ModelInterface;
use \Phalcon\Mvc\View as PhView;
use Phalcon\Di;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\RelationInterface;
class BaseModel extends PhModel 
{
	public static $tableName;
	public function initialize()
	{
		
	}

	public static function findQuery($sql = null)
	{

		$container = Di::getDefault();
		$query     = new Query(
			$sql,
			$container
		);

		return $query->execute();
	}
	
	
}
class BaseController extends PhController
{
	public static $controller = '';
	public static $action = '';
	public $aResult = null;

	public function beforeExecuteRoute($dispatcher)
	{
		self::$controller = $dispatcher->getControllerName();
		self::$action = $dispatcher->getActionName();

		try {
			$this->db;
		} catch (PhException $e) {
			return $Return['msg'][] = "cannot connect to database";
		} catch (\PDOException $e) {
			return $Return['msg'][] = $e->getMessage();
		}
	}

	public static function getCtrlName()
	{
		return self::$controller;
	}

	public static function getActionlName()
	{
		return self::$action;
	}


	public function onConstruct()
	{
	}
	public function initialize()
	{
	}
	public function RedisClear($key)
	{

		$keys = $this->redis->getkeys($key);
		foreach ($keys as $Item) {
			$delKey = str_replace($this->redis->getPrefix(), "", $Item);
			$this->redis->delete($delKey);
		}
	}
	public static function viewAction($index, $Action, $aResult)
	{

		$view = new PhView();
		$view->setViewsDir("phalcon/views/");
		
		return $view->getRender(
			$index,
			$Action,
			$aResult,
			function ($view) {
				$view->setRenderLevel(PhView::LEVEL_LAYOUT);
			}
		);
	}

	public function beforeDispatch($dispatcher)
	{
	}

	public function afterDispatch($dispatcher)
	{
	}



	public function afterExecuteRoute($dispatcher)
	{
		if (!empty($Return)) {
			echo JSON_encode($Return, JSON_UNESCAPED_UNICODE);
		}
		exit;
	}
}
