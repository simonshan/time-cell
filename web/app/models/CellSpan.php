<?php

use Phalcon\Mvc\Model;

class CellSpan extends Model {
    public $id;
    public $schemaId;
    public $cellSpanBegin;
    public $cellSpanEnd;
    public $seq;
    
    public function initialize() {
    	$this->setSource("cellSpan");
    }
}