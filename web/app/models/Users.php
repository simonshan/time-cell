<?php

use Phalcon\Mvc\Model;

class Users extends Model {
    public $id;
    public $name;
    public $passwd;
    public $email;
    public $mobile;
    
    public function initialize() {
    	$this->setSource("users");
    }
}