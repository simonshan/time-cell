<?php

use Phalcon\Mvc\Model;

class Tasks extends Model {
    public $id;
    public $userId;
    public $task;
    public $timeAdded;
    public $timeDone;
    public $status;
    public $cellDate;
    public $cellTime;
    
    public function initialize() {
    	$this->setSource("tasks");
    }
}