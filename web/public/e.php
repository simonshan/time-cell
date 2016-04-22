<?php
function v(){
	echo "<pre>";
	var_dump(func_get_args());
	echo "</pre>";	
}

function _v(){
	echo "<pre>";
	var_dump(func_get_args());
	echo "</pre>";
	exit;
}

function p(){
	echo "<pre>";
	print_r(func_get_args());
	echo "</pre>";
}

function _p(){
	echo "<pre>";
	print_r(func_get_args());
	echo "</pre>";
	exit;
}