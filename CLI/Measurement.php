<?php

require_once './Serial.php';
require_once './Log.php';

/**
 * Handles the communication with the MCP39F501 to measure the current, voltage and power
 *
 * @author Stefan Fixl
 */
class Measurement {
    
    // Commands:
    const COM_HEADER = 0xA5;
    const COM_SELECT = 0x4C;
    const COM_DESELECT = 0x4B;
    const COM_SETADRESS = 0x41;
    const COM_REGREAD16 = 0x52;
    const COM_REGREAD32 = 0x44;
    const COM_ACKNOWLEDGE = 0x06;
    const COM_REGREADN = 0x4E;
    // Registers
    const REG_CURRENT_RMS = 0x04;
    const REG_VOLTAGE_RMS = 0x08;
    const REG_ACTIVE_POWER = 0x0A;
    const REG_REACTIVE_POWER = 0x0E;
    const REG_APPARENT_POWER = 0x12;
    // Device addresses
    private static $ADDRS = [0x4C, 0x4D, 0x4E, 0x4F];

    /**
     * Read a value from the given device
     * @param integer $device 0-3
     * @param integer $reg_addr one of the REG_ constants
     * @return value
     */
/*    public function read($device, $reg_addr) {
        Log::info($this, "read from device $device at addr: $reg_addr");
        $command = chr(self::COM_SELECT);
        $command .= chr(self::$ADDRS[$device]);
        $this->serial($command);

        $command = chr(self::COM_SETADRESS);
        $command .= chr(0x00) . chr($reg_addr);
        $bits = 32;
        switch ($reg_addr) {
            case self::REG_CURRENT_RMS:
            case self::REG_ACTIVE_POWER:
            case self::REG_REACTIVE_POWER:
            case self::REG_APPARENT_POWER:
                $command .= chr(self::COM_REGREAD32);
                break;

            case self::REG_CURRENT_RMS:
                $command .= chr(self::COM_REGREAD16);
                $bits = 16;
                break;

            default:
                Log::error($this, "Error: invalid register address: " . $reg_addr);
                $bits = -1;
                //    throw new Exception("Measurement Error: invalid register address"); //could cause problem: device is not deselected
                break;
        }

        $ret = $this->serial($command, true, 0.1);
        Log::info($this, "requested address $reg_addr from device $device - returned '$ret' = " . $this->debugCommand($command, false));

        $command = chr(self::COM_DESELECT);
        $command .= chr(self::$ADDRS[$device]);
        $this->serial($command);

        $format = "V"; //32 Bit little endian
        if ($bits == 16) {
            $format = "v"; //16 bit little endian
        }
        $value = unpack($format, $this->getData($ret));
        return $value;
    }
 */

    /**
     * Read all registers that contain useful data
     * @param integer $device 0-3
     * @return array(5) with integers
     */
    public function readAll($device) {
        Log::info($this, "read everyting from device $device");

        $command = chr(self::COM_SELECT);
        $command .= chr(self::$ADDRS[$device]);
        $this->serial($command, true);
        
        $command = chr(self::COM_SETADRESS);
        $command .= chr(0x00) . chr(self::REG_CURRENT_RMS); //start at CURRENT
        $command .= chr(self::COM_REGREADN) . chr(18); // read 18 bytes
        $ret = $this->serial($command, true, 0.07);
        Log::info($this, "requested everything from device $device ... returned:(".strlen($ret).") " . $this->debugCommand($ret, false));
        
        $command = chr(self::COM_DESELECT);
        $command .= chr(self::$ADDRS[$device]);
        $this->serial($command, false);
        
        if ($ret) {
            $value = $this->getData($ret);
            $data = unpack("Vcurrent/vvoltage/Vactive/Vreactive/Vapparent", $value);
            return $data;
        } else {
            return array(
                "current" => 0,
                "voltage" => 0,
                "active" => 0,
                "reactive" => 0,
                "apparent" => 0
                );
        }
    }

    /**
     * Calculate the checksum of a command
     * @param string $command
     */
    private function getChecksum($command) {
        $sum = 0;
        for ($i = 0; $i < strlen($command); $i++) {
            $sum += ord($command[$i]);
        }
        $sum = $sum % 256;
        return $sum;
    }

    /**
     * Add the header, the length and the checksum to a command
     * @param string $command
     * @return string 
     */
    private function formCommand($command) {
        $size = strlen($command) + 3; //command size + header + size + checksum
        $fullcommand = chr(self::COM_HEADER) . "" . chr($size) . $command;
        $chksum = $this->getChecksum($fullcommand);
        $fullcommand .= chr($chksum);

        return $fullcommand;
    }

    /**
     * Write a command to the serial port
     * @param string $command
     * @return boolean
     */
    public function serial($command, $want_answer = true, $waitTime = 0) {
        $command = $this->formCommand($command);
        Log::info($this, "serial - send command: " . $this->debugCommand($command, true));
        $serial = Serial::getInstance();
        $serial->write($command);

        if (!$want_answer) {
            return false;
        }
        $ret = $serial->read(0, $waitTime);
        //var_dump($ret);
        if (!$ret) {
            Log::alert($this, "serial - no answer - command: " . $this->debugCommand($command, true));
            return false;
        }
        if (strlen($ret) == 0 /*|| ord($ret[0]) !== self::COM_ACKNOWLEDGE*/) {
            Log::critical($this, "serial - invalid answer:" . $this->debugCommand($ret, false) . " - command: " . $this->debugCommand($command, true));
            return false;
        }
        return $ret;
    }

    /**
     * Remove the header, the length and the checksum from a command (usually the reply)
     * @param string $command
     * @return string
     */
    private function getData($command) {
        $ret = "";
        $size = strlen($command);
        for ($i = 2; $i < $size - 1; $i++) {
            $ret .= $command[$i];
        }
        return $ret;
    }

    /**
     * Make a command readeable for humans
     * @param string $command
     * @param boolean $send if the command was for sending or if it was recieved
     * @return string
     */
    private function debugCommand($command, $send) {
        $readable = "";
        for ($i = 0; $i < strlen($command); $i++) {
            $cmd = ord($command[$i]);     
            if ($send) {
                if ($cmd === self::COM_DESELECT) {
                    $readable .= "DESELECT ";
                } else if ($cmd === self::COM_HEADER) {
                    $readable .= "HEADER ";
                } else if ($cmd === self::COM_REGREAD16) {
                    $readable .= "REGREAD16 ";
                } else if ($cmd === self::COM_REGREAD32) {
                    $readable .= "REGREAD32 ";
                } else if ($cmd === self::COM_SELECT) {
                    $readable .= "SELECT ";
                } else if ($cmd === self::COM_SETADRESS) {
                    $readable .= "SETADRESS ";
                } else if ($cmd === self::REG_ACTIVE_POWER) {
                    $readable .= "ACTIVE_POWER ";
                } else if ($cmd === self::REG_APPARENT_POWER) {
                    $readable .= "APPARENT_POWER ";
                } else if ($cmd === self::REG_CURRENT_RMS) {
                    $readable .= "CURRENT_RMS ";
                } else if ($cmd === self::REG_REACTIVE_POWER) {
                    $readable .= "REACTIVE_POWER ";
                } else if ($cmd === self::REG_VOLTAGE_RMS) {
                    $readable .= "VOLTAGE_RMS ";
                } else {
                    $readable .= "0x" . dechex($cmd) . " ";
                }
            }else if ($cmd === self::COM_ACKNOWLEDGE) {
                $readable .= "ACKNOWLEDGE ";
            }else{
                $readable .= "0x" . dechex($cmd) . " ";
            }
        }
        return $readable;
    }
}
    
    