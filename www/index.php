<?php
require_once 'CMS.php';
require_once 'DBManager.php';
require_once 'Chart.php';

/**
 * The index page that shows the chart
 * @author Stefan Fixl
 */


$db = filter_input(INPUT_GET, "db", FILTER_SANITIZE_STRING);
$start = filter_input(INPUT_GET, "start", FILTER_SANITIZE_STRING);
$end = filter_input(INPUT_GET, "end", FILTER_SANITIZE_STRING);
$device = filter_input(INPUT_GET, "device", FILTER_SANITIZE_NUMBER_INT);
if(! $db){
    $db = "DayMeasurements";
}
if(!$end){
    $end = "now";
}
if(!$start){
    switch($db){
        case "MonthMeasurements": $start = "2678400"; break;
        case "YearMeasurements": $start = "31536000"; break;
        case "DayMeasurements": 
        default:
            $start = "86400";
            break;
    }
}
if(! $device || $device > 3 || $device < 0){
    $device = 0;
}
$pagename = "";
switch($db){
    case "MonthMeasurements": 
        $pagename = "Monats - Messwerte"; 
        break;
    case "YearMeasurements": 
        $pagename = "Jahres - Messwerte"; 
        break;
    case "DayMeasurements": $pagename = "Tages-Messwerte"; break;
    default:
        $pagename = "DoNotHackThis";
        $db = "DayMeasurements"; 
        break;
}
$pagename .= "  - Gerät $device";
CMS::startPage(true, $pagename);
CMS::insertMenu($device, $db);

Chart::htmlChart();
//$result = DBManager::readDevice(DBManager::date("2000-01-01"), DBManager::now(),0, "*");
//var_dump($result);
Chart::initChart($db, $start, $end, $device);
//Chart::initChart(DBManager::readDevice(DBManager::date("2000-01-01"), DBManager::now(),1, "*"));


CMS::endPage(); 