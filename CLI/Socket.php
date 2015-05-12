<?php
require_once './MainLoop.php';

/**
 * A class that opens a server socket, which can be used to control the program
 * @author Stefan Fixl
 */
class Socket{
    private $ip = "127.0.0.1";
    private $port = 8085;
    private $socket;
    /**
     * A reference to the MainLoop (the parent class)
     * @var MainLoop
     */
    private $mainLoop;
    /**
     * The constructor - called by MainLoop
     * @param MainLoop $mainloop
     */
    public function __construct($mainloop) {
        $this->mainLoop = $mainloop;

        // Create a TCP Stream socket 
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
        socket_bind($this->socket, $this->ip, $this->port);
        socket_listen($this->socket); 
        socket_set_nonblock($this->socket); 

    }
    
    /**
     * Destructor - close the socket
     */
    public function __destruct() {
        // Close the master socket 
        socket_close($this->socket); 
    }
    /**
     * Check for incoming messages
     * This function is called in the MainLoop every second
     */
    public function check() {

        $client = null;
        // accept incoming sockets
        if ($client = @socket_accept($this->socket)) {
            if (!is_resource($client)) {
                return;
            }
            $string = null;
            // read incoming data
            if (@socket_recv($client, $string, 2048, MSG_DONTWAIT) === 0) {
                // close socket if message length is zero
                unset($client);
                socket_close($client);
            } else if ($string) {
                /*
                 * If a new message arrived, split it up in command and parameters
                 * and pass it to onCommand(...)
                 */
                $string = strtolower($string);
                // TODO: double space removal does not work
           //     $string = str_replace($string, "\t", " ");
           //     $string = str_replace($string, "  ", " "); 
            
                if($string[strlen($string)-1] === "\n"){
                    $string = substr($string, 0, strlen($string)-1);
                }
                
                $commands = explode("\n", $string);
                foreach ($commands as $command) {
                    $words = explode(" ", $command);
                    $num_words = count($words);
                    if ($num_words >= 1) {
                        if ($num_words > 1) {
                            $params = array();
                            for ($i2 = 1; $i2 < $num_words; $i2++) {
                                $params[] = $words[$i2];
                            }
                        } else {
                            $params = null;
                        }
                        $this->onCommand($words[0], $params, $client);
                    }
                }
            }
        }
    }

    /**
     * This function is called when the socket has a new message
     * @param String $command
     *          following commands are supported:
     *          "gc" - get configuration (returns json formatted configuration)
     *          "sc" - set configuration (see onCommandSetConfig for more details)
     *          "scd"- reset configuration to default (see onCommandSetDefault for more details)
     *          "gv" - get value(s) (see onCommandGetValues for more details)
     * @param Array $parameters
     * @param Socket $client
     */
    private function onCommand($command, $parameters, $client){
        switch($command){
            /**
             * Get Configuration
             * No parameters
             */
            case "gc": 
                $this->write($client, file_get_contents("relais.json"));
                break;
            //set config
            /**
             * Set configuration
             * Parameter: 
             * 1: device (default or 0-3)
             * 2: option (script, parameters, override)
             * 3ff: value(s)
             */
            case "sc":
                if($parameters != null){
                    $ret = $this->onCommandSetConfig($parameters);
                    if($ret !== false){
                        $this->write($client, $ret);
                    }
                }
                break;
            /**
             * Set config to default
             */
            case "scd":
                $ret = $this->onCommandSetDefault($parameters);
                if($ret !== false){
                        $this->write($client, $ret);
                    }
                break;
            /**
             * Get Values
             */
            case "gv":
                $this->write($client, $this->onCommandGetValues($parameters));
                //$this->write($client, $this->mainLoop->getDevice(0)->getCurrent());
                break;
            default: 
                $this->write($client, "Unknown command: $command");
                Log::warning($this, "Unknown command: $command"); 
                break;
        }
    }
    /**
     * Set the configuration according to the command
     * @param Array $parameters an array of 3 or more parameters
     *          parameter 1: device number [0-3]
     *          parameter 2: option ("script", "parameters" or "override")
     *          parameter 3(and following): depending on the option 
     *              script: script name(file) 
     *              parameters: script parameters
     *              override [-1, 0, 1]
     * @return false if no errors occured
     *         else error message
     */
    private function onCommandSetConfig($parameters){
        if(count($parameters) < 3){
            $msg = "SetConfig: parameter count invalid: ".count($parameters);
            Log::warning($this, $msg);
            return $msg;
        }
            //var_dump($parameters);
            $device = $parameters[0];
            $option = $parameters[1];
            $params = array();
            for($i = 2; $i < count($parameters); $i++){
                $params[] = $parameters[$i];
            }
            $config = RelaisController::getInstance()->getRelaisConfiguration();
            /**
             * type RelaisConfiguration
             */
            $rcon = null;
            switch($device){
                
                case 0: 
                case 1: 
                case 2: 
                case 3:
                    $rcon = $config->getRelaisConf(intval($device));
                    break;
                case "default": 
                    $rcon = $config->getDefaultConf(); 
                    break;
                default:
                    $msg = "SetConfig: parameter 1 (device) invalid: $device";
                    Log::warning($this, $msg);
                    return $msg;
            }
            $changed = false;
            switch($option){
                case "script": 
                    $changed = $rcon->setScript($params[0]);
                    break;
                case "parameters": 
                    $changed = $rcon->setParameters($params);
                    break;
                case "override": 
                    $changed = $rcon->setOverride(intval($params[0]));
                    break;
                default:
                    $msg = "SetConfig: parameter 2 (option) invalid: $option";
                    Log::warning($this, $msg);
                    return $msg;
            }
            if($changed){
                $config->writeConfigFile();
                RelaisController::getInstance()->checkStatus();
            }
            return false;
    }
    /**
     * Set the configuration of one device to the default value
     * @param array $parameters
     *          Only one parameter is allowed:
     *          [0-3] device number
     * @returns false if the input is valid
     *          error message if an error occured
     */
    private function onCommandSetDefault($parameters){
       if($parameters === null 
                || count($parameters) !== 1 
                || ! is_numeric($parameters[0])){
           $msg = "Error: invalid parameter(s): ".var_export($parameters, true);
           Log::warning($this, $msg);
           return $msg;
       }
       switch ($parameters[0]) {
            case 0:
            case 1:
            case 2:
            case 3:
                $config = RelaisController::getInstance()->getRelaisConfiguration();
                $config->setToDefaultConf($parameters[0]);
                break;

            default:
                $msg = "Error: invalid parameter vlaue: ".strval($parameters[0]);
                Log::warning($this, $msg);
                return $msg;
       }
       return false;
    }
    /**
     * This function is called on the command "gv".
     * Depending on $parameters the value(s) of one or all devices are returned.
     * @param array $parameters
     *          Possible parameters:
     *          [0-3] The device number
     *          [string] which value (e.g. "current") - see getDeviceValues() for further details
     * 
     *          Allowed combinations:
     *          [device number] [string]
     *          [device number]
     *          [string]
     *          The device number parameter has always to be in front of the value name.
     * @return string json formatted data or error message
     */
    private function onCommandGetValues($parameters){
        $values = array();
        $single_device = ($parameters !== null && is_numeric($parameters[0]));
        $num_params = count($parameters);
        if($single_device){
            
            if($num_params > 1)
            {
                $values[] = $this->getDeviceValues($parameters[0], $parameters[1]); 
            }else{
                $values[] = $this->getDeviceValues($parameters[0], null);
            }  
        }else{
            for($i = 0; $i < 4; $i++){
                if($num_params > 1){
                    $values[$i] = $this->getDeviceValues($i, $parameters[1]);
                }else if($num_params > 0){
                    $values[$i] = $this->getDeviceValues($i, $parameters[0]);
                }else{
                    $values[$i] = $this->getDeviceValues($i, null);
                }
            }
        }
        $json = json_encode($values);
        if($json){
        //    echo $json; 
            return $json; 
        }else{
            $msg = "Get Values: Error with json_encode:".json_last_error_msg();
            Log::error($this, $msg);
            return $msg;
        }
    }
    /**
     * Get a value from the given device.
     * @param integer $device [0-3]
     * @param string $param which value to return
     *          The following values are possible:
     *          "current"
     *          "currentmin"
     *          "currentmax"
     *          "voltage"
     *          "voltagemin"
     *          "voltagemax"
     *          "active"
     *          "activemin"
     *          "activemax"
     *          "reactive"
     *          "reactivemin"
     *          "reactivemax"
     *          "apparent"
     *          "apparentmin"
     *          "apparentmax"
     *          If the parameter is null or different to these values, all values are returned.
     * @return string 
     */
    private function getDeviceValues($device, $param){
        $value = null;
        $device = $this->mainLoop->getDevice(intval($device));
        switch($param){
            case "current": $value = $device->getCurrent(); break;
            case "currentmin": $value = $device->getCurrentMin(); break;
            case "currentmax": $value = $device->getCurrentMax(); break;
            case "voltage": $value = $device->getVoltage(); break;
            case "voltagemin": $value = $device->getVoltageMin(); break;
            case "voltagemax": $value = $device->getVoltageMax(); break;
            case "active": $value = $device->getActivePower(); break;
            case "activemin": $value = $device->getActivePowerMin(); break;
            case "activemax": $value = $device->getActivePowerMax(); break;
            case "reactive": $value = $device->getReactivePower(); break;
            case "reactivemin": $value = $device->getReactivePowerMin(); break;
            case "reactivemax": $value = $device->getReactivePowerMax(); break;
            case "apparent": $value = $device->getApparentPower(); break;
            case "apparentmin": $value = $device->getApparentPowerMin(); break;
            case "apparentmax": $value = $device->getApparentPowerMax(); break;
            default: $value = $device->getValues(); break; 
        }
        return $value;
    }
    
    /**
     * Write a sting to the socket
     * @param resource $socket
     * @param string $string
     */
    private function write($socket, $string){
         socket_write($socket, $string, strlen($string));
         Log::debug($this, "answer to client: ".$string);
    }

}
