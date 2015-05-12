<?php

/**
 * A class that handles the measurements of one device
 * @author Stefan Fixl
 */
class MeasureDevice {

    /**
     * The MeasureValue for the current
     * @var type MeasureValue
     */
    private $current = null;

    /**
     * The MeasureValue for the voltage
     * @var type MeasureValue
     */
    private $voltage = null;

    /**
     * The MeasureValue for the active power
     * @var type MeasureValue
     */
    private $active_power = null;

    /**
     * The MeasureValue for the reactive power
     * @var type MeasureValue
     */
    private $reactive_power = null;

    /**
     * The MeasureValue for the apparent power
     * @var type MeasureValue
     */
    private $apparent_power = null;
    
    /**
     * Initalizes the members 
     */
    public function __construct() {
        $this->current = new MeasureValue;
        $this->voltage = new MeasureValue;
        $this->active_power = new MeasureValue;
        $this->reactive_power = new MeasureValue;
        $this->apparent_power = new MeasureValue;
    }

    /**
     * Add the values to get the average measurements
     * @param array $values numeric array with the order current-voltage-active-reactive-apparent
     */
    public function add($values) {
        $this->current->add($values["current"]);
        $this->voltage->add($values["voltage"]);
        $this->active_power->add($values["active"]);
        $this->reactive_power->add($values["reactive"]);
        $this->apparent_power->add($values["apparent"]);
    }
    /**
     * Add a current to create the average measurement
     * @param float $value
     */
    public function addCurrent($value) {
        $this->current->add($value);
    }
    /**
     * Add a voltage to create the average measurement
     * @param float $value
     */
    public function addVoltage($value) {
        $this->voltage->add($value);
    }
    /**
     * Add a active power to create the average measurement
     * @param float $value
     */
    public function addActivePower($value) {
        $this->active_power->add($value);
    }
    /**
     * Add a reactive power to create the average measurement
     * @param float $value
     */
    public function addReactivePower($value) {
        $this->reactive_power->add($value);
    }
    /**
     * Add a apparent power to create the average measurement
     * @param float $value
     */
    public function addApparentPower($value) {
        $this->apparent_power->add($value);
    }
    /**
     * Normalize the measurements (devides the sum of the measurements through the number of iterations)
     */
    public function normalize() {
        $this->current->normalize();
        $this->voltage->normalize();
        $this->active_power->normalize();
        $this->reactive_power->normalize();
        $this->apparent_power->normalize();
    }
    /**
     * Get the average current
     * @param boolean $normalize normalize the measurement AND overwrite the old value to calculate the tolerance
     * @return float
     */
    public function getCurrent($normalize = false) {

        return $this->current->get($normalize);
    }
    /**
     * Get the average voltage
     * @param boolean $normalize normalize the measurement AND overwrite the old value to calculate the tolerance
     * @return float
     */
    public function getVoltage($normalize = false) {

        return $this->voltage->get($normalize);
    }
    /**
     * Get the average active power
     * @param boolean $normalize normalize the measurement AND overwrite the old value to calculate the tolerance
     * @return float
     */
    public function getActivePower($normalize = false) {
        return $this->active_power->get($normalize);
    }
    /**
     * Get the average reactive power
     * @param boolean $normalize normalize the measurement AND overwrite the old value to calculate the tolerance
     * @return float
     */
    public function getReactivePower($normalize = false) {
        return $this->reactive_power->get($normalize);
    }
    /**
     * Get the average apparent power
     * @param boolean $normalize normalize the measurement AND overwrite the old value to calculate the tolerance
     * @return float
     */
    public function getApparentPower($normalize = false) {
        return $this->apparent_power->get($normalize);
    }
    /**
     * Check if one of the measurements has changed (if it is or is not within the tolerance)
     * @return boolean changed
     */
    public function changed() {
        return ($this->current->changed() 
                || $this->voltage->changed() 
                || $this->active_power->changed() 
                || $this->reactive_power->changed() 
                || $this->apparent_power->changed()
                );
    }
    /**
     * Set the toleance for the current (+- value within it is seen as "the same")
     * @param float $value
     */
    public function setCurrentTolerance($value){
        $this->current->setTolerance($value);
    }
    /**
     * Set the toleance for the voltage (+- value within it is seen as "the same")
     * @param float $value
     */
    public function setVoltageTolerance($value){
        $this->voltage->setTolerance($value);
    }
    /**
     * Set the toleance for the active power (+- value within it is seen as "the same")
     * @param float $value
     */
    public function setActivePowerTolerance($value){
        $this->active_power->setTolerance($value);
    }
    /**
     * Set the toleance for the reactive power (+- value within it is seen as "the same")
     * @param float $value
     */
    public function setReactivePowerTolerance($value){
        $this->reactive_power->setTolerance($value);
    }
    /**
     * Set the toleance for the apparent power (+- value within it is seen as "the same")
     * @param float $value
     */
    public function setApparentPowerTolerance($value){
        $this->apparent_power->setTolerance($value);
    }
    /**
     * Get the minimum current
     * @return float
     */
    public function getCurrentMin(){
        return $this->current->getMin();
    }
    /**
     * Get the maximum current
     * @return float
     */
    public function getCurrentMax(){
        return $this->current->getMax();
    }
    /**
     * Get the minimum voltage
     * @return float
     */
    public function getVoltageMin(){
        return $this->voltage->getMin();
    }
    /**
     * Get the maximum voltage
     * @return float
     */
    public function getVoltageMax(){
        return $this->voltage->getMax();
    }
    /**
     * Get the minimum active power
     * @return float
     */
    public function getActivePowerMin(){
        return $this->active_power->getMin();
    }
    /**
     * Get the maximum active power
     * @return float
     */
    public function getActivePowerMax(){
        return $this->active_power->getMax();
    }
    /**
     * Get the minimum reactive power
     * @return float
     */
    public function getReactivePowerMin(){
        return $this->reactive_power->getMin();
    }
    /**
     * Get the maximum reactive power
     * @return float
     */
    public function getReactivePowerMax(){
        return $this->reactive_power->getMax();
    }
    /**
     * Get the minimum apparent power
     * @return float
     */
    public function getApparentPowerMin(){
        return $this->apparent_power->getMin();
    }
    /**
     * Get the maximum apparent power
     * @return float
     */
    public function getApparentPowerMax(){
        return $this->apparent_power->getMax();
    }
    
    /**
     * Get all values as an associative array
     * @return array
     */
    public function getValues(){
        $arr = array();
        $arr["Current"] = $this->getCurrent();
        $arr["CurrentMin"] = $this->getCurrentMin();
        $arr["CurrentMax"] = $this->getCurrentMax();
        $arr["Voltage"] = $this->getVoltage();
        $arr["VoltageMin"] = $this->getVoltageMin();
        $arr["VoltageMax"] = $this->getVoltageMax();
        $arr["Active"] = $this->getActivePower();
        $arr["ActiveMin"] = $this->getActivePowerMin();
        $arr["ActiveMax"] = $this->getActivePowerMax();
        $arr["Reactive"] = $this->getReactivePower();
        $arr["ReactiveMin"] = $this->getReactivePowerMin();
        $arr["ReactiveMax"] = $this->getReactivePowerMax();
        $arr["Apparent"] = $this->getApparentPower();
        $arr["ApparentMin"] = $this->getApparentPowerMin();
        $arr["ApparentMax"] = $this->getApparentPowerMax();
        return $arr;
    }

}
