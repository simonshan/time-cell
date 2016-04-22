<?php

class ErrorController extends ControllerBase
{

    public function show404Action(){
    	echo '404, page not found';
    }
    
    public function show503Action(){
    	echo '503, server error';
    }
}