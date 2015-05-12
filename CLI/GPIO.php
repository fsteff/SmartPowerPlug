<?php


/**
 * A class that handles the GPIOs
 * @author Stefan Fixl
 */
class GPIO {
    /**
     * An array that contains the GPIO numbers
     * @var array 
     */
    private static $MAP = [
        0 => 18,
        1 => 23,
        2 => 24,
        3 => 25,
    ];
    /**
     * Initalize the gpios to use them with the relais
     */
    public static function initRelais()
    {
        for($i = 0; $i < 4; $i++)
        {
            if(! file_exists("/sys/class/gpio/gpio".self::$MAP[$i]."/value")){
                file_put_contents("/sys/class/gpio/export", self::$MAP[$i]);
                file_put_contents("/sys/class/gpio/gpio".self::$MAP[$i]."/direction", "out");
            }
            
        }     
         
    }
    /**
     * Set the value of a GPIO
     * Needs to be initalized before!
     * @param int $gpio relais number
     * @param int $value 
     */
    public static function setRelais($gpio, $value)
    {
        file_put_contents("/sys/class/gpio/gpio".self::$MAP[$gpio]."/value", $value);
    }
    
}
