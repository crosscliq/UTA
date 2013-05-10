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
	$params['minutesout'] =  90;
	$params['onwardcalls'] = 'true';
	$params['filterroute'] = '';
	$this->response = $this->call('StopMonitor', $params);
	//var_dump($this->response->StopMonitoringDelivery->MonitoredStopVisit);
	//var_dump($this->response);
	return $this;
}

function renderStopMonitoring() {
	$r = new stdClass;
	$r->responseTimestamp = (string) $this->response->StopMonitoringDelivery->ResponseTimestamp;
	$r->validUntil =(string) $this->response->StopMonitoringDelivery->ValidUntil;

	$r->stopName =(string) $this->response->StopMonitoringDelivery->Extensions->StopName;
	$r->stopLat =(string) $this->response->StopMonitoringDelivery->Extensions->StopLongitude;
	$r->stopLng =(string) $this->response->StopMonitoringDelivery->Extensions->StopLatitude;
	$r->items = array();
	foreach ($this->response->StopMonitoringDelivery->MonitoredStopVisit->MonitoredVehicleJourney as $bus) {
		$obj = new stdClass;
		$obj->classes =array();
		$obj->classes[] = $this->makeProcessRateClass($bus->ProgressRate);	
		$obj->status = $this->makeProcessRateText($bus->ProgressRate);
		$obj->num = (string) $bus->LineRef;
		$obj->dest = (string) $bus->DirectionRef;
		$obj->loc = array("lat"=> (float) $bus->VehicleLocation->Latitude, "lng"=> (float) $bus->VehicleLocation->Longitude);	
		$obj->eta = (int) $bus->MonitoredCall->Extensions->EstimatedDepartureTime;
		$r->items[] = $obj;
	}	
	
	return $r;

}
/* FROM UTA
	the progress rate flag indicates the following states as of the 
last chance on 3/21/12 

    0 = Early - vehicle is running early 
    1 = On Time - vehicle is running on stated to up to + 4 minutes 59 
seconds behind 
    2 = Late - vehicle is greater than 4:59 to 9:59 late 
    3 = Critical Late - vehicle is greater than 10 minutes late 
    4 = Critical Early - vehicle is greater than 10 minutes early 
    5 = Not Set - no data available */

function makeProcessRateText($rate) {
	$process = null;
	switch ($rate) {
		case '0':
			$process = 'Early';
			break;
		case '1':
			$process = 'On Time';
			break;
		case '2':
			$process = 'Late';
			break;
		case '3':
			$process = 'Critical Late';
			break;
		case '4':
			$process = 'Critical Early';
			break;
		default:
			$process = 'Not Set';
			break;
	}
	return $process;
	
}
function makeProcessRateClass($rate) {
	$process = null;
	switch ($rate) {
		case '0':
			$process = 'early';
			break;
		case '1':
			$process = 'ontime';
			break;
		case '2':
			$process = 'late';
			break;
		case '3':
			$process = 'criticallate';
			break;
		case '4':
			$process = 'criticalearly';
			break;
		default:
			$process = 'noprocess';
			break;
	}
	return $process;
	
}

function CloseStopMonitoring () {
	
}

public function call($url, $params) {

        $params['usertoken'] = $this->apikey;
      	$fields_string = '';
        foreach($params as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string,'&');



		$ch = $this->ch;
		curl_setopt($ch, CURLOPT_URL, $this->root . $url . '?' . $fields_string);
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

