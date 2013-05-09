<?php 


class UTAApi {


    public $ch;
    public $root = 'http://api.rideuta.com/SIRI/SIRI.svc';
    public $debug = false;
    public $apikey = "UNE1P00BQW7";
    public $response = null;

 public function __construct($apikey=null) {
 	if(!$apikey) $apikey = $this->apikey;


 	$this->ch = curl_init();
    curl_setopt($this->ch, CURLOPT_USERAGENT, 'CrossCliq-PHP/1.0.13');
    curl_setopt($this->ch, CURLOPT_POST, true);
    curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($this->ch, CURLOPT_HEADER, false);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);


    $this->root = rtrim($this->root, '/') . '/';
 }

public function __destruct() {
        curl_close($this->ch);
    }

function VehicleMonitoringbyRoute () {

}

function VehicleMonitoringbyVehicle () {
	
}

function StopMonitoring ($stopid) {
	$params = array();
	$params['stopid'] = $stopid;
	$params['minutesout'] =  30;
	$params['onwardcalls'] = 'true';
	$params['filterroute'] = '';
	$this->response = $this->call('StopMonitor', $params);
	var_dump($this->response->StopMonitoringDelivery->MonitoredStopVisit->MonitoredVehicleJourney[0]->MonitoredCall->Extensions->EstimatedDepartureTime);
	die();
	return $this->response;
}

function CloseStopMonitoring () {
	
}

public function call($url, $params) {

        $params['usertoken'] = $this->apikey;
        $url = $this->root . $url;
        foreach($params as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string,'&');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url . '?' . $fields_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        $start = microtime(true);
        $this->log('Call to ' . $this->root . $url . ' ' . $params);
        if($this->debug) {
            $curl_buffer = fopen('php://memory', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $curl_buffer);
        }

        $response_body = curl_exec($ch);
        $info = curl_getinfo($ch);
        $time = microtime(true) - $start;
        if($this->debug) {
            rewind($curl_buffer);
            $this->log(stream_get_contents($curl_buffer));
            fclose($curl_buffer);
        }
        $this->log('Completed in ' . number_format($time * 1000, 2) . 'ms');
        $this->log('Got response: ' . $response_body);

        if(curl_error($ch)) {
            error_log("API call to $url failed:  ". curl_error($ch));
        }
        $result = simplexml_load_string(curl_exec($ch));
       
        if($result === null) { die('empty response');};
        

        return $result;
    }

	public function log($msg) {
        if($this->debug) error_log($msg);
    }


}





?>


<html>
<h1><?php $api = new UTAApi();
$api->StopMonitoring('125424'); ?></h1>
<html>