<?php

require_once './CMS.php';
require_once './SocketClient.php';

CMS::startPage(false);
CMS::insertMenu();

$postdata = array();
$postdata["Default"] = array();
$postdata["R1"] = array();
$postdata["R2"] = array();
$postdata["R3"] = array();
$postdata["R4"] = array();
foreach($postdata as $key => $device){
    $postdata[$key]["script"] = filter_input(INPUT_POST, "script$key", FILTER_SANITIZE_STRING);
    $postdata[$key]["override"] = filter_input(INPUT_POST, "override$key", FILTER_SANITIZE_NUMBER_INT);
    $postdata[$key]["parameters"] = filter_input(INPUT_POST, "parameters$key", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
}

if($postdata["Default"]["script"] !== NULL){
    $arr = $postdata;
    $sock = new SocketClient();
    $sock->connect("127.0.0.1", 8085);
    $config = "";
    $names = array("default", "0" ,"1" , "2", "3");
    $i = 0;
    foreach ($arr as $key => $device){
        $config .= "sc $names[$i] script ".$device["script"]. "\n";
        $config .= "sc $names[$i] override ".$device["override"]."\n";
        $config .= "sc $names[$i] parameters ";
        foreach ($device["parameters"] as $param){
            if($param != ""){
                $config .= str_replace(' ', '',$param) ." ";
            }
        }
        if($i < 5){
            $config .= "\n";
        }
        $i++;
    }
    $sock->write($config);
    echo $sock->read();
    $sock->close();
    
}else{
    $sock = new SocketClient();
    $sock->connect("127.0.0.1", 8085);
    $sock->write("gc");
    $json = $sock->read();
    if($json != ""){
        $arr = json_decode($json, true);
    }else{
        echo "Error: cannot connect to the server!";
    }
    
    $sock->close(); 
}

echo "<form action='settings.php' method='POST'>";
echo "<table class='table-settings'>";
$i = 0;
foreach ($arr as $key => $device) {
    echo "<div class='device-settings'>";
    echo "<thead><th>";
    echo "Relais $key: </th><th></th></thead>";
    echo "<tbody></tr><tr><td><label for='script$key'>Überprüfungs-Script: </label></td>";
    echo "<td><input type='text' name='script$key' value='".$device["script"]."'>"; 
    if(!file_exists("/etc/SmartPlug/".$device["script"])){
        echo "<div class='ui-icon ui-icon-alert'></div><div class='file-not-found'>Script nicht gefunden!</div>";
    }
    echo "</td>";
    echo "</tr><tr><td><label for='override$key'>Ausgangswert überschreiben: </label></td>";
    echo "<td><input type='number' name='override$key' value='".$device["override"]."' min='-1' max='1'></td>";
    echo "</tr><tr><td><label for='parameters$key'>Parameter: </label></td><td>";
    foreach($device["parameters"] as $pkey => $param){
        if($param != ""){
            echo "<div class='input-params-div'><input type='text' class='input-parameters' name='parameters".$key."[]' value='$param'></div>";
        }
        
    }
    echo "<div name='plus-button-parameters' class='ui-icon ui-icon-circle-plus'></div>";
    echo "</td></tr>";
    echo "</tbody></div>";    
    $i++;
}
echo "<thead><th></th><th></th></thead>";
 echo "<tbody><tr class='save'><td></td><td><input type='submit' name='Speichern' value='Speichern'></td></tbody>";
 echo "</form></table>";
 
 echo '<script src="js/settings.js"></script>';
 CMS::endPage();