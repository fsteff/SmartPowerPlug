<?php
require_once './DBManager.php';

/**
 * Returns the json-formatted chart data
 * Thought to be used with AJAX
 * @author Stefan Fixl
 */

// get the GET data
$db = filter_input(INPUT_GET, "db", FILTER_SANITIZE_STRING);
$start = filter_input(INPUT_GET, "start", FILTER_SANITIZE_STRING);
$end = filter_input(INPUT_GET, "end", FILTER_SANITIZE_STRING);
$select = filter_input(INPUT_GET, "select", FILTER_SANITIZE_STRING);
$device = filter_input(INPUT_GET, "device", FILTER_SANITIZE_NUMBER_INT);

// if some data was not provided was or invalid, use default values
if(! $db){
    $db = "DayMeasurements";
}
if($end){
    $end = DBManager::date($end);
}else{
    $end = DBManager::now();
}
if($start){
    if(is_numeric($start)){
        $start = DBManager::secondsBeforeNow($start);
    }else{
        $start = DBManager::date($start);
    }
}else{
    $start = DBManager::date("2000-01-01");
}
if(! $select){
    $select = "*";
}
if(! $device){
    $device = 0;
}

// get the data from the database
$data = DBManager::readDevice($db, $start, $end,$device, $select);
$entries = count($data);

// build the page (json data)
header('Content-Type: application/json');

echo "[";
echo '{"name": "Wirkleistung","data":[';
for($i = 0; $i < $entries; $i++){
    echo '{"x":'.$data[$i]["datetime"].',"y":'.$data[$i]["active"].'}';
    if($i != $entries - 1){
        echo ',';
    }
}
echo ']},';
echo '{"name": "Blindleistung","data":[';
for($i = 0; $i < $entries; $i++){
    echo '{"x":'.$data[$i]["datetime"].',"y":'.$data[$i]["reactive"].'}';
    if($i != $entries - 1){
        echo ',';
    }
}
echo ']},';
echo '{"name": "Max. Wirkleistung","data":[';
for($i = 0; $i < $entries; $i++){
    echo '{"x":'.$data[$i]["datetime"].',"y":'.$data[$i]["activeMax"].'}';
    if($i != $entries - 1){
        echo ',';
    }
}
echo ']},';
echo '{"name": "Min. Wirkleistung","data":[';
for($i = 0; $i < $entries; $i++){
    echo '{"x":'.$data[$i]["datetime"].',"y":'.$data[$i]["activeMin"].'}';
    if($i != $entries - 1){
        echo ',';
    }
}
echo ']},';
echo '{"name": "Max. Blindleistung","data":[';
for($i = 0; $i < $entries; $i++){
    echo '{"x":'.$data[$i]["datetime"].',"y":'.$data[$i]["reactiveMax"].'}';
    if($i != $entries - 1){
        echo ',';
    }
}
echo ']},';
echo '{"name": "Min. Blindleistung","data":[';
for($i = 0; $i < $entries; $i++){
    echo '{"x":'.$data[$i]["datetime"].',"y":'.$data[$i]["reactiveMin"].'}';
    if($i != $entries - 1){
        echo ',';
    }
}
echo ']}';


echo "]";