<?php

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

class SecurityPlugin extends Plugin
{
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        // Take the active controller/action from the dispatcher
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        if('time_cell' == $controller){
	        $user = $this->session->get('user');
	        if (!$user['id']) {
	        	$this->flash->error("请登录后使用时间格子");
	        	$dispatcher->forward([
	        		'controller' => 'user',
	        		'action'     => 'index'
	        	]);
	        	return false;
	        }
        }
    }
}