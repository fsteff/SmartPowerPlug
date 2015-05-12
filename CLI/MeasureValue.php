<?php
/**
 * Handles the measured values and calculates an average value
 * It is also possible to check if the value has changed (if it is within the tolerance)
 * 
 * @author Stefan Fixl
 */
class MeasureValue {
    /**
     * The sum of the measurements
     * Used to calculate the average value ($value / $iter)
     * @var float
     */
    private $value = 0.0;
    /**
     * Number of measurements added to $value
     * @var float 
     */
    private $iter = 1;
    /**
     * The old value (the last time it was asked by get())
     * @var integer
     */
    private $old = 0.0;
    /**
     * The highest value
     * @var float
     */
    private $max = 0.0;
    /**
     * The lowest value
     * @var type 
     */
    private $min = 0.0;
    /**
     * The tolerance for determining if the value was "changed"
     * The tolerance is a factor -> 0.01 = 1% change
     * @var float
     */
    private static $tolerance = 0.01;
    
    
    /**
     * Check if the value was changed (if it is within the tolerance)
     * @return boolean
     */
    public function changed(){
        $value = $this->getValue();
        return ($value > ($this->old + $this->old * self::$tolerance) 
                || $value < ($this->old - $this->old * self::$tolerance));
    }
    
    /**
     * Get the average value
     * @return type
     */
    private function getValue(){
        if($this->iter != 1){
            return $this->value / $this->iter;
        }else{
            return $this->value;
        } 
    }
    /**
     * Calculate the average value and write it to $value
     */
    public function normalize(){
        if($this->iter != 1){
            $this->value /= $this->iter;
            $this->iter = 1;
        
        }
        $this->min = $this->value;
        $this->max = $this->value;
        
        $this->old = $this->value;
    }
    /**
     * Get the average value and normalize $value if $normalize is true 
     * If normalize is true, overwrite $old with the current value 
     * @param boolean $normalize
     * @return float
     */
    public function get($normalize = false){
        if($normalize){
            $this->normalize();
        }
        $value = $this->getValue();
        if($normalize){
            $this->old = $value;
        }
        return $value;
    }
    /**
     * Get the lowest measured value (since the last time it was normalized)
     * @return float
     */
    public function getMin(){
        return $this->min;
    }
    /**
     * Get the hightest measured value (since the last time it was normalized)
     * @return float
     */
    public function getMax(){
        return $this->max;
    }
    
    /**
     * Add a measurement to $value to calculate the average 
     * @param type $value
     */
    public function add($value){
        if($this->max < $value){
            $this->max = $value;
        }
        if($this->min > $value){
            $this->min = $value;
        }
        
        $this->value += $value;
        $this->iter++;
    }
    /**
     * Set the tolerance to check if the value was "changed"
     * @param float $value
     */
    public function setTolerance($value){
        self::$tolerance = $value;
    }
}
