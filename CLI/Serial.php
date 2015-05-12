<?php

require_once 'libs/PhpSerial.php';

/**
 * A class that handles serial communication
 *
 * @author Stefan Fixl
 */
class Serial {
        
    /**
     * The static instance of this class
     * @var RelaisController
     */
    static private $instance = null;
    /**
     * @var type phpSerial
     */
    public $serial = null;
    
    // The settings for the serial port:
    public $device = "/dev/ttyAMA0";
    public $baudRate = 4800;
    public $parity = "none";
    public $charLength = 8;
    public $stopBit = 1;
    public $waitTime = 0.02;
    
    /**
     * Constructor of the class
     * Do not use this!
     * Use the static ::getInstance!
     */
    private function __construct() {
        
    }
    /**
     * Returns the static instance of this class
     * The first time this function is called, it calls the constructor
     * @return RelaisController
     */
    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
   /**
    * Open the serial port and configurate it to work with a MCP39F501
    * @return boolean success
    */
    public function connect() {
        if($this->serial !== null)
        {
            Log::error($this, "Serial is already connected!");
        }
        
        $this->serial = new phpSerial;
        $this->serial->deviceSet($this->device);
        $this->serial->confBaudRate($this->baudRate);
        $this->serial->confParity($this->parity);
        $this->serial->confCharacterLength($this->charLength);
        $this->serial->confStopBits($this->stopBit);
        
        $success = $this->serial->deviceOpen();  
        if(! $success){
            Log::error($this, "Could not open serial port ". $this->device);
            $this->serial = null;
        }
        return  $success;
    }
    /**
     * Write a message out of the serial port
     * If the port was not already opened, open it
     * @param string $message
     */
    public function write($message)
    {
        if($this->serial == null){
            $this->connect();
        }
        $this->serial->sendMessage($message, $this->waitTime);    
    }
    /**
     * Read from the serial port
     * (If the port was not already opened, open it)
     * @param int $count maximum number of bytes to read
     * @param float $waitTime time to wait in seconds
     * @return string received data
     */
    public function read($count = 0, $waitTime = 0)
    {
        usleep((int) ($waitTime * 1000000));
        if($this->serial == null){
            $this->connect();
        }
        return $this->serial->readPort($count);
    }
    /**
     * Close the serial port
     */
    public function close()
    {
        if($this->serial == null){
            return;
        }
        $ret =  $this->serial->deviceClose();
        if($ret !== true){
            Log::error($this, "Could not close the serial port");
        }
        $this->serial = null;
    }
    /**
     * Check if the serial port is opened
     * @return type
     */
    public function isConnected()
    {
        return ($this->serial !== null);
    }

}
