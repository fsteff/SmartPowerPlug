#!sudo /usr/bin/php -q
<?php

error_reporting(E_ALL);
require_once "System/Daemon.php";
require_once "Serial.php";

// Bare minimum setup
System_Daemon::setOption("appName", "smart_plug");
System_Daemon::setOption("authorEmail", "sfixl@htl-steyr.ac.at");
System_Daemon::setOption("logLocation", "/etc/SmartPlug/SmartPlugCLI.log");  
System_Daemon::setOption("authorName", "Stefan Fixl");
System_Daemon::setOption("appDescription", "Smart Plug Steuer-Daemon");


//System_Daemon::setOption("appDir", dirname(__FILE__)); 
// Spawn Deamon!

System_Daemon::start();
file_put_contents("SmartPlugCLI.log", "Startup\n");


Log::setMinLogLevel(4);

$loop = new MainLoop();
$loop->run();

System_Daemon::stop();