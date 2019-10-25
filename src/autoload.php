<?php

function autoload_google_spreadsheet ($name){
	$path = __DIR__ . "/" . str_replace("_", "/", $name) . ".php";
	if(file_exists($path)){
		require_once($path);
	}
}

spl_autoload_register('autoload_google_spreadsheet');
