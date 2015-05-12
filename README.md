# SmartPowerPlug
A Raspberry PI based power plug that measures the energy.
It is able to turn on/off the power if necessary, based on a shell script to be highly customizeable.

The webinterface displays the measured values on an interactive graph (using Rickshaw).

------------------------------------------------------------------------
SOFTWARE:
------------------------------------------------------------------------
Needed programs:
 - Apache 2 (or a similar webserver)
 - php5 and php-cli
 - php5-sqlite
 - SQLite3

PHP Dependencies:
 - PhpSerial
 - System_Daemon
 
JS Dependencies:
 - Rickshaw
 - D3
 - JQuery
 - JQuery UI
 - Menumaker

All the dependencies are included in the source.


------------------------------------------------------------------------
HARDWARE:
------------------------------------------------------------------------
The energy measurement is based on the MCP39F501, which communciates over RS485.

