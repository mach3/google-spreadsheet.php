<?php

function __autoload_google_spreadsheet__ ($name){
	$path = __DIR__ . "/" . str_replace("_", "/", $name) . ".php";
	if(file_exists($path)){
		require_once($path);
	}
}

spl_autoload_register(__autoload_google_spreadsheet__);
