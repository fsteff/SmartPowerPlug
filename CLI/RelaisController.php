<?php
require_once './RelaisConfiguration.php';

/**
 * Controls the relais according to the script an the parameters
 *
 * @author Stefan Fixl
 */
class RelaisController {
    /**
     * The static instance of this class
     * @var RelaisController
     */
    static private $instance = null;
    /**
     * The current relais value
     * @var Array Integer
     */
    private $relais = Array(0, 0, 0, 0);
    /**
     * The configuration of all relais
     * @var AllRelaisConfiguration
     */
    private $config;
    /**
     * Constructor of the class
     * Do not use this!
     * Use the static ::getInstance!
     */
    private function __construct() {
        $this->config = new AllRelaisConfigurations();
        GPIO::initRelais();
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
     * Check the status of the relais and change it if neccessary 
     */
    public function checkStatus() {
        $this->config->reloadIfChanged();
        $time = date('H') * 60 + date('i');

        for ($i = 0; $i < 4; $i++) {
            // if override > 0, use this value
            
            $override = $this->config->getRelaisConf($i)->getOverride();
            if ($override >= 0) {
                if($override > 1){
                    $override = 1;
                }
                GPIO::setRelais($i, $override);
            }
            // else (if override < 0) check script
            else{
                $this->checkScript($i, $time);
            }
        }
    }
    /**
     * Easier to use static function for checkStatus()
     */
    public static function check(){
        RelaisController::getInstance()->checkStatus();
    }
    /**
     * Starts a script to check if the relais should be changed
     * @param integer $i relais number
     * @param integer $time time in minutes of the day
     */
    private function checkScript($i, $time) {
        $exec = "sh " . $this->config->getRelaisConf($i)->getScript() . " $time";
        foreach ($this->config->getRelaisConf($i)->getParameters() as $value) {
            $exec .= ' "' . $value . '"';
        }
        $tmp = exec($exec);
        if ($tmp != $this->relais[$i]) {
            GPIO::setRelais($i, $tmp);
            $this->relais[$i] = $tmp;
        }
    }
    /**
     * Get the all relais configuration class
     * @return AllRelaisConfiguration
     */
    public function &getRelaisConfiguration()
    {
        return $this->config;
    }
}
