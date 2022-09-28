<?php

use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

class SecurityPlugin extends Plugin
{
	// ...

	public function beforeExecuteRoute(
		Event $event,
		Dispatcher $containerspatcher
	) {
		$auth = $this->session->get('auth');

		if (!$auth) {
			$role = 'Guests';
		} else {
			$role = 'Users';
		}

		$controller = $containerspatcher->getControllerName();
		$action     = $containerspatcher->getActionName();

		$acl = $this->getAcl();

		$allowed = $acl->isAllowed($role, $controller, $action);
		if (true !== $allowed) {
			$this->flash->error(
				"You do not have access to this module"
			);

			$containerspatcher->forward(
				[
					'controller' => 'index',
					'action'     => 'index',
				]
			);

			return false;
		}
	}
}
/*
class Security extends Plugin
{

	public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
    	
    	
    }

    public function beforeException(Event $event, Dispatcher $dispatcher, $exception)
    {
    	
    	//Handle 404 exceptions
    	if ($exception instanceof DispatchException) {
    		$this->flash->error("Handle 404 exceptions");
			return $dispatcher->forward(array(
                'controller' => $dispatcher->getControllerName(),
                'action' => 'notFound'
            ));
    	}
    
    	//Handle other exceptions
		// $this->flash->error("Handle 503 exceptions");
		return $dispatcher->forward(array(
			'controller' => $dispatcher->getControllerName(),
			'action' => 'error'
		));
    	return false;
    }    
    
	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
    	
    	$role = 'Users';
    	$privateResources = array();
    	
    	
    	
       
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();


        $acl = $this->getAcl($privateResources);
		
        
        $allowed = $acl->isAllowed($role, $controller, $action);
      
        if (  $allowed != Acl::ALLOW ) {
        	 
        	return $this->dispatcher->forward(array(
        			'controller' => 'index',
        			'action' => 'index'
        	));
        }

        
        
    }
    
    public function getAcl($privateResources = array())
    {
    	$publicResources = array();
    	
    
	    
		$acl = new Phalcon\Acl\Adapter\Memory();
		
		
		$acl->setDefaultAction(Phalcon\Acl::DENY);
		
		$acl->addRole(new Phalcon\Acl\Role('Users'));
		
		$publicResources['api'] = array('*');
		$publicResources['cron'] = array('*');
		$publicResources['index'] = array('index');
		$publicResources['test'] = array('*');
       
		foreach ($publicResources as $resource => $actions) {
			$acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
		}
		
		foreach ($publicResources as $resource => $actions) {				
			foreach ($actions as $action) {
				$acl->allow('Users', $resource, $action);
			}
		}
		
		foreach ($privateResources as $resource => $actions) {
			$acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
		}
		//Grant access to private area only to role Users
		foreach ($privateResources as $resource => $actions) {
			foreach ($actions as $action) {
				$acl->allow('Users', $resource, $action);
			}
		}
		
		return $acl;
    }

}