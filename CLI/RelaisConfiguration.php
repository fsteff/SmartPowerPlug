<?php
/**
 * This class holds the configuration of one relais (can also be the default configuration)
 * @author Stefan Fixl
 */
class RelaisConfiguration {
    /**
     * Checking script name
     * @var string
     */
    private $script;        
    /**
     * Parameters for the script
     * @var type 
     */
    private $parameters;    
    /**
     * Override setting
     * < 0: no override
     *   0: override with 0
     * > 1: overide with 1 
     * @var integer 
     */
    private $override;      
    /**
     * Constructor
     * @param String $script script name
     * @param Array $parameters
     * @param int $override
     */
    public function __construct($script, $parameters, $override) {
        $this->script = $script;
        $this->parameters = $parameters;
        $this->override = $override;
    }
    /**
     * Returns the script name
     * @return string
     */
    public function getScript() {
        return $this->script;
    }
    /**
     * Returns the parameters
     * @return array
     */
    public function getParameters() {
        return $this->parameters;
    }
    /**
     * Get one parameter
     * @param int $i
     * @return string
     */
    public function getParameter($i)
    {
        return $this->parameters[$i];
    }
    /**
     * Get the override
     * @return int
     */
    public function getOverride() {
        return $this->override;
    }
    /**
     * Set the script - returns true if it was changed
     * @param string $script
     * @return boolean changed
     */
    public function setScript($script) {
        if($script !== $this->script){
            $this->script = $script;
            return true;
        }
        return false;
    }
    /**
     * Set the parameters - returns true if they were changed
     * @param array $parameters
     * @return boolean changed
     */
    public function setParameters($parameters) {
        if($parameters !== $this->parameters){
            $this->parameters = $parameters;
            return true;
        }
        return false;
    }
    /**
     * Add a parameter
     * @param string $parameter
     */
    public function addParameter($parameter) {
        $this->parameters[] = $parameter;
    }
    /**
     * Set the override - returns true if it was changed
     * @param int $value
     * @return boolean changed
     */
    public function setOverride($value) {
        if($value !== $this->override){
            $this->override = $value;
            return true;
        }
        return false;
    }
    /**
     * Returns the settings as an associative array
     * @return array
     */
    public function toArray()
    {
        $arr = Array();
        $arr["script"] = $this->script;
        $arr["parameters"] = $this->parameters;
        $arr["override"] = $this->override;
        return $arr;
    }

}
