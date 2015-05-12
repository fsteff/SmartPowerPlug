<?php
require_once "System/Daemon.php";

/**
 * Fully static class to write logs 
 * For each log function use e.g. Log::debug($this, "message");
 * The name of the caller class is added in front of the message
 * @author Stefan Fixl
 */
class Log {
    /**
     * log level from 1 to 8
     * messages under this level are ignored
     * @var integer 
     */
    private static $minLogLevel = 4;
    /**
     * Write a debug message (log level 1)
     * @param object $caller
     * @param string $message
     */
    public static function debug($caller, $message){
        if(self::$minLogLevel >= 1){return;}
        System_Daemon::log(System_Daemon::LOG_DEBUG, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a info message log level 2) 
     * @param object $caller
     * @param string $message
     */
    public static function info($caller, $message){
        if(self::$minLogLevel >= 2){return;}
        System_Daemon::log(System_Daemon::LOG_INFO, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a notice message log level 3)
     * @param object $caller
     * @param string $message
     */
    public static function notice($caller, $message){
        if(self::$minLogLevel >= 3){return;}
        
        System_Daemon::log(System_Daemon::LOG_NOTICE, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a warning message log level 4)
     * @param object $caller
     * @param string $message
     */
    public static function warning($caller, $message){
        if(self::$minLogLevel >= 4){return;}
        System_Daemon::log(System_Daemon::LOG_WARNING, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a critical message log level 5)
     * @param object $caller
     * @param string $message
     */
    public static function critical($caller, $message){
        if(self::$minLogLevel >= 5){return;}
        System_Daemon::log(System_Daemon::LOG_CRIT, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a alert message log level 6)
     * @param object $caller
     * @param string $message
     */
    public static function alert($caller, $message){
        if(self::$minLogLevel >= 6){return;}
        System_Daemon::log(System_Daemon::LOG_ALERT, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a error message log level 7)
     * @param object $caller
     * @param string $message
     */
    public static function error($caller, $message){
        if(self::$minLogLevel >= 7){return;}
        System_Daemon::log(System_Daemon::LOG_ERR, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Write a emergency message log level 8)
     * @param object $caller
     * @param string $message
     */
    public static function emergency($caller, $message){
        if(self::$minLogLevel >= 8){return;}
        System_Daemon::log(System_Daemon::LOG_EMERG, Log::getCaller($caller) .": ". $message);
    }
    /**
     * Set the minimum log level
     * @param integer $level
     */
    public static function setMinLogLevel($level){
        self::$minLogLevel = $level;
    }
    /**
     * Get the minimum log level
     * @return integer log level
     */
    public static function getMinLogLevel(){
        return self::$minLogLevel;
    }
    /**
     * Get tha name of the calling class
     * @param class|string $caller
     * @return string
     */
    private static function getCaller($caller){
        if(is_string($caller)){
            return $caller;
        }else{
            return get_class($caller);
        }
    }
}
