<?php

use \Phalcon\Config\Adapter\Ini as PhConfig;
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager;
use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;


ini_set('display_errors', '1');
error_reporting(E_ALL);

function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'].'/';
    return $protocol.$domainName;
}
define( 'SITE_URL', siteURL() );

$dirname = explode("/", dirname(__FILE__));
if(strpos(dirname(__FILE__),"xampp") != false ){
	$dirname = explode("\\", dirname(__FILE__));	
}

$dirname = array_pop($dirname);

$ConfigFile = "config.ini";
$dir = "/{$dirname}/";

if (!defined('ROOT_PATH') && file_exists("/home/cfd888/public_html")) 
{
	// define('ROOT_PATH', "/var/www" . $dir);
	define('ROOT_PATH', "/home/cfd888/public_html" . $dir);	
}

if (!defined('ROOT_PATH') && file_exists("/var/www")) 
{
	define('ROOT_PATH', "/var/www" . $dir);
}




if (!defined('CONFIGFILE_PATH')) {
	define("CONFIGFILE_PATH", ROOT_PATH . 'phalcon/config/' );
}
require_once ROOT_PATH . '/phalcon/extend/Extendphalcon.php'; // controller view 基本extand

$ConfigFile = CONFIGFILE_PATH.$ConfigFile;
if(!is_file($ConfigFile)) {
	echo $ConfigFile ." FAILD";
	$images = glob(CONFIGFILE_PATH.'*.{ini}', GLOB_BRACE);
	var_dump($images);	
	exit;
}


// Create the new object
$config = new PhConfig($ConfigFile);
define('INIT_RESET', $config->init->reset);
//設定前端讀取網址
define('DOMAIN_VUE', $config->domain->vue);
define('DOMAIN_DEV', $config->domain->dev);
define('DOMAIN_CRON', $config->domain->cron);

$loader = new Loader();

$loader->registerDirs(
	[
		ROOT_PATH . $config->application->controllersDir,
		ROOT_PATH . $config->application->pluginsDir,
		ROOT_PATH . $config->application->modelsDir,
		ROOT_PATH . $config->application->modelsDir.'DIYA_DATA/',
		ROOT_PATH . $config->application->modelsDir.'phalcon4/',
		ROOT_PATH . $config->application->modelsDir.'account/',
		ROOT_PATH . $config->application->libraryDir,
	]
);

    

$loader->register();

$container = new FactoryDefault();

$container->set(
	'view',
	function () {
		$view = new View();
		$view->setViewsDir(
			'./phalcon/views/'
		);
		$view->registerEngines(
			[
				'.phtml' => Volt::class,
			]
		);
		return $view;
	}
);



$container->set('db', function () use ($config) {
	return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => $config->database->name,
		'options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $config->database->charset)
	));
});
$container->set('DIYA_DATA', function () use ($config) {
	return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
		"host" => $config->database->host,
		"username" => $config->database->username,
		"password" => $config->database->password,
		"dbname" => "DIYA_DATA",
		'options' => array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $config->database->charset)
	));
});
 
$container->setShared('MongoDB', function () use ($config) {

	$manager = new MongoDB\Driver\Manager("mongodb://{$config->mangodb->username}:{$config->mangodb->password}@{$config->mangodb->host}:27017/{$config->mangodb->name}");

	return $manager;
});


$container->setShared('redis', function () use ($config) {
	$serializerFactory = new SerializerFactory();

	$options = [
		'defaultSerializer' => 'Php',
		'lifetime'          => 86400,
		'host'              => 'localhost',
		'port'              => 6379,
		'index'             => 1,
	];

	return new Redis($serializerFactory, $options);
});

$container->set(
	'Mcached',
	function () use ($config) {
		$Mcached = new Mcached();
		$Mcached::$prefix = $config->Mcached->prefix;
		if (empty($Mcached::$prefix)) {
			throw Exception("need set prefix");
		}
		$Mcached->addServer($config->Mcached->host, $config->Mcached->port);
		$Mcached->setOption($Mcached::OPT_PREFIX_KEY, $Mcached::$prefix);
		$Mcached->setOption($Mcached::OPT_HASH, $Mcached::HASH_MD5);
		return $Mcached;
	}
);

$container->set(
	'modelsCache',
	function () {
		$serializerFactory = new SerializerFactory();
		$adapterFactory    = new AdapterFactory($serializerFactory);

		$options = [
			'defaultSerializer' => 'Php',
			'lifetime'          => 7200
		];

		$adapter = $adapterFactory->newInstance('apcu', $options);

		return new Cache($adapter);
	}
);

$application = new Application($container);

try {
	$response = $application->handle(
		str_replace($dir ,"",$_SERVER["REQUEST_URI"]),
	);
	
	$response->send();
} catch (\Exception $e) {
	echo $e->getMessage();
}
