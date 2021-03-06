<?php

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

class ExceptionsPlugin
{
    public function beforeException(Event $event, Dispatcher $dispatcher, $exception)
    {
        // Handle 404 exceptions
        if ($exception instanceof DispatchException) {
            $dispatcher->forward(array(
                'controller' => 'error',
                'action'     => 'show404',
            ));
            return false;
        }

        // Handle other exceptions
//         $dispatcher->forward(array(
//             'controller' => 'error',
//             'action'     => 'show503',
//         ));

        echo $exception->getMessage();	
        return false;
    }
}