<?php
# error display
error_reporting(E_ERROR);

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config\Adapter\Ini as ConfigIni;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager as EventsManager;

require_once 'e.php';
define('APP_PATH', realpath('..') . '/');

try {
	// Read the configuration
	$config = new ConfigIni(APP_PATH . 'app/config/config.ini');
	
	// Create a DI
	$di = new FactoryDefault();
	
    // Register an autoloader
    $loader = new Loader();
	$loader->registerDirs(
	    array(
	        APP_PATH . $config->application->controllersDir,
	        APP_PATH . $config->application->pluginsDir,
	        APP_PATH . $config->application->libraryDir,
	        APP_PATH . $config->application->modelsDir,
	        APP_PATH . $config->application->formsDir,
	    )
	)->register();

    // Setup the view component
    $di->set('view', function () use ($config) {
    	$view = new View();
    	$view->setViewsDir(APP_PATH . $config->application->viewsDir);
    	return $view;
    });
    
    // Setup url
	$di->set('url', function () use ($config) {
		$url = new UrlProvider();
		$url->setBaseUri($config->application->baseUri);
		$url->setStaticBaseUri($config->application->staticUri);
		$url->venderUri = $config->application->venderUri;
		return $url;
	});
    
    // Setup the database service
    $di->set('db', function () use ($config) {
    	return new DbAdapter((array)($config->database));
    });
    
	// Start the session the first time a component requests the session service
	$di->set('session', function () {
		$session = new Session();
		$session->start();
		return $session;
	});

	// MVC dispatcher
	$di->set('dispatcher', function () {
		// Create an events manager
	    $eventsManager = new EventsManager();
	
	    // Listen for events produced in the dispatcher using the Security plugin
	    $eventsManager->attach('dispatch:beforeExecuteRoute', new SecurityPlugin());
	
	    // Handle exceptions and not-found exceptions using NotFoundPlugin	
	    $eventsManager->attach('dispatch:beforeException', new ExceptionsPlugin());
	
	    $dispatcher = new Dispatcher();
	    
	    // Assign the events manager to the dispatcher
	    $dispatcher->setEventsManager($eventsManager);
	
	    return $dispatcher;
	});
	
	// set router
	$di->set('router', function(){
		$router = new Phalcon\Mvc\Router();
		$router->removeExtraSlashes(true);
		$router->setDefaultController('home');
		return $router;
	}, true);
	
    // Handle the request
    $app = new Application($di);
    echo $app->handle()->getContent();
    
} catch (\Exception $e) {
     echo "Exception: ", $e->getMessage();
}