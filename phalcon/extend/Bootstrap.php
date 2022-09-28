<?php

use \Phalcon\Config\Adapter\Ini as PhConfig;
use \Phalcon\Loader as PhLoader;
use \Phalcon\Mvc\Application as PhApplication;
use \Phalcon\Exception as PhException;

use Phalcon\Cache\Frontend\Data as FrontData;


class Bootstrap
{
	private $_di;

	/**
	 * Constructor
	 *
	 * @param $di
	 */
	public function __construct($di)
	{
		$this->_di = $di;
	}

	/**
	 * Runs the application performing all initializations
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	public function init($options)
	{
		$loaders = array(
				'config', // 載入ini 欓專用config讀取功能
				'loader', //定義controller view 相關目錄
				'dispatcher',
				'view', // 預設一定要有view
				'database',
				'Mcache',
				'Cache',
		);


		try {

			foreach ($loaders as $service)
			{
				$function = 'init' . ucfirst($service);

				$this->$function($options);
			}

			
			
			$application = new PhApplication();
			$application->setDI($this->_di);

			return $application->handle()->getContent();

		} catch (PhException $e) {
			echo $e->getMessage();
		} catch (\PDOException $e) {
			echo $e->getMessage();
		}
	}

	// Protected functions

	/**
	 * Initializes the config. Reads it from its location and
	 * stores it in the Di container for easier access
	 *
	 * @param array $options
	 */
	protected function initConfig($options = array())
	{
		$configFile = CONFIGFILE_PATH;

		// Create the new object
		$config = new PhConfig($configFile);

		// Store it in the Di container
		$this->_di->set('config', $config);
	}

	/**
	 * Initializes the loader
	 *
	 * @param array $options
	 */
	protected function initLoader($options = array())
	{
		$config = $this->_di->get('config');

		// Creates the autoloader
		$loader = new PhLoader();

		$loader->registerNamespaces([
			'App\Controllers' => ROOT_PATH . $config->application->controllersDir,
			'App\Libraries' => ROOT_PATH . $config->application->libraryDir,
		])->register();

		$loader->registerDirs(
				array(
						ROOT_PATH . $config->application->controllersDir,
						ROOT_PATH . $config->application->pluginsDir,
						ROOT_PATH . $config->application->modelsDir,
						ROOT_PATH . $config->application->libraryDir,
				)
		);
	
		$loader->register();
	}
	
	protected function initDispatcher($options = array())
	{
		
		$config = $this->_di->get('config');
		$di     = $this->_di;
	
		$di->set('dispatcher', function() use ($di) {

	    $eventsManager = $di->getShared('eventsManager');
	    $security = new Security($di);

	    $eventsManager->attach('dispatch', $security);
	    
	    $dispatcher = new baseDispatcher();	    
	    $dispatcher->setEventsManager($eventsManager);	
	    	return $dispatcher;
		});
	}

	protected function initView($options = array())
	{
		$config = $this->_di->get('config');
		$di     = $this->_di;

		//Setup the view component
		$di->set('view', function() use ($config){
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir($config->application->viewsDir);
			$view->registerEngines(array(
					".volt" => 'Phalcon\Mvc\View\Engine\Volt',
					".phtml" => 'Phalcon\Mvc\View\Engine\Volt'
			));
		
			return $view;
		});
	}
	
	protected function initDatabase($options = array())
	{
		$config = $this->_di->get('config');
		$di     = $this->_di;
	
		
		//Setup the database service
		$di->set('db', function() use ($config){
			return new BaseMysql(array(
					"host" => $config->database->host,
					"username" => $config->database->username,
					"password" => $config->database->password,
					"dbname" => $config->database->name,
					'charset'   =>$config->database->charset,
			));
		});

		$di->set('bryant_office_for_shop', function() use ($config) {
			return new BaseMysql(array(
				"host" => $config->bryant_office_for_shop->host,
				"username" => $config->bryant_office_for_shop->username,
				"password" => $config->bryant_office_for_shop->password,
				"dbname" => $config->bryant_office_for_shop->name,
				'charset'   =>$config->bryant_office_for_shop->charset,
			));
		});
	}
	
	


	protected function initMcache($options = array())
	{
		$config = $this->_di->get('config');
		$di     = $this->_di;

		// Set the models cache service
		$di->set(
			'Mcached',
			function () use ($config) {
				$Mcached = new Mcached();
				$Mcached::$prefix = $config->Mcached->prefix;
				if (empty($Mcached::$prefix)) {
					throw Exception("need set prefix");
				}
				$Mcached->addServer($config->Mcached->host,$config->Mcached->port);
				$Mcached->setOption($Mcached::OPT_PREFIX_KEY, $Mcached::$prefix);
				$Mcached->setOption($Mcached::OPT_HASH, $Mcached::HASH_MD5);
				return $Mcached;
			}
		);
	}

	protected function initCache($options = array())
	{
		$config = $this->_di->get('config');
		$di     = $this->_di;

		// Set the models cache service
		$di->set(
			'modelsCache',
			function () use ($config) {
				// Cache data for one hour
				$frontCache = new FrontData(
					[
						'lifetime' => $config->Mcached->lifetime,
					]
				);

				// Create the Cache setting memcached connection options
				$cache = new modelsCache(
					$frontCache,
					[							
						'servers' => [
							[
								'host'   => $config->Mcached->host,
								'port'   => $config->Mcached->port,
								'weight' => $config->Mcached->weight,
							],
						],
						'client' => [
							\Memcached::OPT_HASH       => \Memcached::HASH_MD5,
							\Memcached::OPT_PREFIX_KEY => 'admin',
						],
						'persistent_id' => 'admin_cache',
					]
				);

				return $cache;
			}
		);
	}


}

