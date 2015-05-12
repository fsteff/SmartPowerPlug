<?php
require_once './RelaisConfiguration.php';
/**
 * This class holds the configuration of the relais and the default configuration.
 * It loads the configuration from the configuration file.
 * @author Stefan Fixl
 */
class AllRelaisConfigurations {
    /**
     * An array with the configuration for the devices
     * @var Array RelaisConfiguration 
     */
    private $relaisConf = Array();
    /**
     * The default configuration
     * @var RelaisConfiguration
     */
    private $defaultConf;
    /**
     * The name of the configuration file
     * @var string
     */
    private $configFile = "relais.json";
    /**
     * The date/time the configuration file was changed the last time
     * @var inttimestamp
     */
    private $lastChange;
    /**
     * Constructor - reads the settings from the config file by calling readConfigFile
     */
    public function __construct() {
        $this->readConfigFile();
        
    }
    /**
     * Reads the settings from the config file and writes them to defaultConf and relaisConf
     * Operators in the parameters [== != > < <= >= || &&] 
     * are replaced by the shell script equals [-eq -ne -gt -lt -le -ge -o -a]
     * @return boolean success
     */
    private function readConfigFile() {
        $this->lastChange = filemtime($this->configFile);
        $json = file_get_contents($this->configFile);
        if(! $json){
            System_Daemon::log(System_Daemon::LOG_ERROR, "can not open $this->configFile");
            return false;
        }
        $ini = json_decode($json, TRUE);
        if(! $ini)
        {
            System_Daemon::log(System_Daemon::LOG_ERROR, "could not decode $this->configFile: ". json_last_error_msg());
            return false;
        }

        $relaisNames = Array("Default", "R1", "R2", "R3", "R4");
        if (count($ini) != 5) {
            System_Daemon::log(System_Daemon::LOG_ERROR, "$this->configFile does not contain 5 relais (default, R1-4)"); 
            return false;
        }
        for ($i = 0; $i < 5; $i++) {
            $relais = $ini[$relaisNames[$i]];
            $params = $this->replace_operators_to_shell($relais["parameters"]);
            if ($i === 0) {
                $this->defaultConf = new RelaisConfiguration(
                        $relais["script"], $params, intval($relais["override"]));
            } else {
                $this->relaisConf[$i-1] = new RelaisConfiguration(
                        $relais["script"], $params, intval($relais["override"]));
                
            }
        }   
        System_Daemon::log(System_Daemon::LOG_INFO, "successfully loaded $this->configFile"); 
        return true;
    }
    /**
     * Write the current configuration to the config file
     * @return boolean success
     */
    public function writeConfigFile() {
        $arr = Array();
        $arr["Default"] = $this->defaultConf->toArray();
        $arr["R1"] = $this->relaisConf[0]->toArray();
        $arr["R2"] = $this->relaisConf[1]->toArray();
        $arr["R3"] = $this->relaisConf[2]->toArray();
        $arr["R4"] = $this->relaisConf[3]->toArray();
        
        $json = json_encode($arr, JSON_PRETTY_PRINT);
        if(! $json)
        {
            System_Daemon::log(System_Daemon::LOG_ERROR, "Error while encoding config to json format: ".json_last_error_msg()); 
            return false;
        }
        $json = $this->replace_operators_to_modern($json);
        if(! file_put_contents($this->configFile, $json)){
            System_Daemon::log(System_Daemon::LOG_ERROR, "Error while writing config $this->configFile"); 
            return false;
        }
        
        System_Daemon::log(System_Daemon::LOG_INFO, "successfully wrote to $this->configFile"); 
        $this->lastChange = filemtime($this->configFile);
        return true;
    }
    /**
     * Reload the config file if it was changed (reads the timestamp)
     */
    public function reloadIfChanged()
    {
        $lastChange = filemtime($this->configFile);
        if($lastChange !== $this->lastChange)
        {
            $this->readConfig();
            System_Daemon::log(System_Daemon::LOG_INFO, "reload relais.json - was modified externally");
        }
    }
    /**
     * Get the configuration of one relais
     * @param integer $relais number (0-3)
     * @return RelaisConfiguration 
     */
    public function getRelaisConf($relais) {
        return $this->relaisConf[$relais];
    }
    /**
     * Get the default configuration
     * @return RelaisConfiguration
     */
    public function getDefaultConf() {
        return $this->defaultConf;
    }
    /**
     * Set the configuration of one relais
     * @param integer $relais index of the relais
     * @param RelaisConfiguration $relaisConfiguration configuration of the relais
     */
    public function setRelaisConf($relais, $relaisConfiguration) {
        $this->relaisConf[$relais] = $relaisConfiguration;
    }
    /**
     * Set the default configuration
     * @param RelaisConfiguration $relaisConfiguration default configuration
     */
    public function setDefaultConf($relaisConfiguration) {
        $this->defaultConf = $relaisConfiguration;
    }
    /**
     * Set a device configuration to default
     * @param type $relais
     */
    public function setToDefaultConf($relais){
        $this->relaisConf[$relais] = clone ($this->defaultConf);
        $this->writeConfigFile();
    }
    /**
     * Replaces c-styled operators with unix shell ones
     * @param string $params
     * @return string
     */
    private function replace_operators_to_shell($params){
        $params = str_replace("==", "-eq", $params);
        $params = str_replace("!=", "-ne", $params);
        $params = str_replace(">=", "-ge", $params);
        $params = str_replace("<=", "-le", $params);
        $params = str_replace(">", "-gt", $params);
        $params = str_replace("<", "-lt", $params);
        $params = str_replace("||", "-o", $params);
        $params = str_replace("&&", "-a", $params);
        return $params;
    }
    /**
     * Replaces unix shell styled operators with c-styled ones
     * @param type $params
     * @return type
     */
    private function replace_operators_to_modern($params){
        $params = str_replace("-eq", "==", $params);
        $params = str_replace("-ne", "!=", $params);
        $params = str_replace("-ge", ">=", $params);
        $params = str_replace("-le", "<=", $params);
        $params = str_replace("-gt", ">", $params);
        $params = str_replace("-lt", "<", $params);
        $params = str_replace("-o", "||", $params);
        $params = str_replace("-a", "&&", $params);
        return $params;
    }

}