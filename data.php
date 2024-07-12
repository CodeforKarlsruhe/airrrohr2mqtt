<?php

// SPDX-License-Identifier: EUPL-1.2
// following source code adapted from https://github.com/opendata-stuttgart/madavi-api/blob/master/data_simple.php 7ebf351

// read sensor ID ('esp8266-'+ChipID)
$headers = array();
if (isset($_SERVER['HTTP_SENSOR'])) $headers['Sensor'] = $_SERVER['HTTP_SENSOR'];
if (isset($_SERVER['HTTP_X_SENSOR']))$headers['Sensor'] = $_SERVER['HTTP_X_SENSOR'];

$json = file_get_contents('php://input');

$results = json_decode($json,true);

header_remove();

$now = gmstrftime("%Y/%m/%d %H:%M:%S");
$today = gmstrftime("%Y-%m-%d");

// copy sensor data values to values array
foreach ($results["sensordatavalues"] as $sensordatavalues) {
	$values[$sensordatavalues["value_type"]] = $sensordatavalues["value"];
}

// print transmitted values
echo "Sensor: ".$headers['Sensor']."\r\n";

// check if data dir exists, create if not
if (!file_exists('data')) {
	mkdir('data', 0755, true);
}

// save data values to CSV (one per day)
$datafile = "data/data-".$headers['Sensor']."-".$today.".csv";

if (!file_exists($datafile)) {
	$outfile = fopen($datafile,"a");
	fwrite($outfile,"Time;durP1;ratioP1;P1;durP2;ratioP2;P2;SDS_P1;SDS_P2;Temp;Humidity;BMP_temperature;BMP_pressure;BME280_temperature;BME280_humidity;BME280_pressure;Samples;Min_cycle;Max_cycle;Signal\n");
	fclose($outfile);
}

if (! isset($values["durP1"])) { $values["durP1"] = ""; }
if (! isset($values["ratioP1"])) { $values["ratioP1"] = ""; }
if (! isset($values["P1"])) { $values["P1"] = ""; }
if (! isset($values["durP2"])) { $values["durP2"] = ""; }
if (! isset($values["ratioP2"])) { $values["ratioP2"] = ""; }
if (! isset($values["P2"])) { $values["P2"] = ""; }
if (! isset($values["SDS_P1"])) { $values["SDS_P1"] = ""; }
if (! isset($values["SDS_P2"])) { $values["SDS_P2"] = ""; }
if (! isset($values["temperature"])) { $values["temperature"] = ""; }
if (! isset($values["humidity"])) { $values["humidity"] = ""; }
if (! isset($values["BMP_temperature"])) { $values["BMP_temperature"] = ""; }
if (! isset($values["BMP_pressure"])) { $values["BMP_pressure"] = ""; }
if (! isset($values["BME280_temperature"])) { $values["BME280_temperature"] = ""; }
if (! isset($values["BME280_humidity"])) { $values["BME280_humidity"] = ""; }
if (! isset($values["BME280_pressure"])) { $values["BME280_pressure"] = ""; }
if (! isset($values["samples"])) { $values["samples"] = ""; }
if (! isset($values["min_micro"])) { $values["min_micro"] = ""; }
if (! isset($values["max_micro"])) { $values["max_micro"] = ""; }
if (! isset($values["signal"])) { $values["signal"] = ""; } else { $values["signal"] = substr($values["signal"],0,-4); }

$outfile = fopen($datafile,"a");
//$debugfile=fopen($datafile."debug","a");
fwrite($outfile,$now.";".$values["durP1"].";".$values["ratioP1"].";".$values["P1"].";".$values["durP2"].";".$values["ratioP2"].";".$values["P2"].";".$values["SDS_P1"].";".$values["SDS_P2"].";".$values["temperature"].";".$values["humidity"].";".$values["BMP_temperature"].";".$values["BMP_pressure"].";".$values["BME280_temperature"].";".$values["BME280_humidity"].";".$values["BME280_pressure"].";".$values["samples"].";".$values["min_micro"].";".$values["max_micro"].";".$values["signal"]."\n");
fclose($outfile);

// MQTT

$mqttmsg=json_encode($values);
$mqtttopic="airrohr/".$headers['Sensor'];

// MQTT config
// define values in config file 
// optionally adapt mqtttopic and mqttmsg
// for template see https://github.com/php-mqtt/client-examples/blob/master/shared/config.php
// following code adapted from https://github.com/php-mqtt/client-examples/blob/master/01_publishing/01_publish_with_qos_0.php 1858fda
// MIT licence



//declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.mqtt.php';

//fwrite($debugfile,$now.";".$mqtttopic.";".$mqttmsg.";".MQTT_BROKER_HOST.":".MQTT_BROKER_PORT."\ninput:".file_get_contents('php://input')."\nsensor API JSON".$json."\n");

//use PhpMqtt\Client\Examples\Shared\SimpleLogger;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;
use Psr\Log\LogLevel;

// Create an instance of a PSR-3 compliant logger. For this example, we will also use the logger to log exceptions.
//$logger = new SimpleLogger(LogLevel::INFO);

try {
    // Create a new instance of an MQTT client and configure it to use the shared broker host and port.
    $client = new MqttClient(MQTT_BROKER_HOST, MQTT_BROKER_PORT, 'airrohr.martinweis.eu', MqttClient::MQTT_3_1, null); //, $logger);
    // Create and configure the connection settings as required.
    $connectionSettings = (new ConnectionSettings)
        ->setUsername(AUTHORIZATION_USERNAME)
        ->setPassword(AUTHORIZATION_PASSWORD);

    // Connect to the broker without specific connection settings but with a clean session.
    $client->connect($connectionSettings, true);

    // Publish the message 'Hello world!' on the topic 'foo/bar/baz' using QoS 0.
    $success=$client->publish($mqtttopic, $mqttmsg, MqttClient::QOS_AT_MOST_ONCE);
    //fwrite($debugfile,"publish success:".$success."\n");

    // Gracefully terminate the connection to the broker.
    $client->disconnect();
} catch (MqttClientException $e) {
    // MqttClientException is the base exception of all exceptions in the library. Catching it will catch all MQTT related exceptions.
    //$logger->error('Publishing a message using QoS 0 failed. An exception occurred.', ['exception' => $e]);
    var_dump($e);
    //fwrite($debugfile,"MqttClientException:".implode($e," ")."\n");
}
//fclose($debugfile);

?>
ok
