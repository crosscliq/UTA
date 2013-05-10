<?php
error_reporting(E_ALL|E_STRICT);ini_set("display_errors",1);// the rest of your script...
$f3=require('lib/base.php');

$f3->config('config.ini');


$f3->route('GET /',
	function($f3) {
		
		
		echo View::instance()->render('home.htm');
	}
);

$f3->route('GET /@stopid',
	function($f3) {
		require('app/api.php');
		$api = new UTAApi();
		$response = $api->StopMonitoring($f3->get('PARAMS.stopid'))->renderStopMonitoring();
		$f3->set('response',$response);
		
		echo View::instance()->render('index.htm');
	}
);


$f3->route('GET|POST /json/@stopid',
	function($f3) {
		
		
		echo View::instance()->render('index.htm');
	}
);


$f3->run();
