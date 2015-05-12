<?php

require_once './DBManager.php';
require_once './Measurement.php';
require_once './RelaisController.php';
require_once './AllRelaisConfigurations.php';
require_once './Socket.php';

/**
 * The whole functionality is controlled by this class
 *
 * @author Stefan Fixl
 */
class MainLoop {
    /**
     * Measurement
     * @var Measurement
     */
    private $measurement;
    /**
     * Array of devices (4)
     * @var Array(MeasureDevice)
     */
    private $devices = array();
    /**
     * How long to wait until the measurements are analyzed
     * @var integer
     */
    private $relaisController;
    
                                    // duration:
    const checkInterval = 300;       // 5 minutes
    
    const monthMultiplicator = 12;   // 1 hour
    
    const yearMultiplicator = 24;    // one day
    
    private $monthIterator = 0;
    private $yearIterator = 0;
    
    private $socket = null;
    
    //development / debug features:
    // use random values instead of measured
    private $_generate_testdata = false;
    // save measured data to the database or not
    private $_save_to_db = true;
    
    /**
     * Start the infinite loop
     */
    public function run() {
        // setup database if it does not exist
        DBManager::checkDBs();
        $this->relaisController = RelaisController::getInstance();
        $this->relaisController->checkStatus();
        
        $this->socket = new Socket($this);
        
        for ($i = 0; $i < 4; $i++) {
            $this->devices[] = new MeasureDevice();
        }
        $this->measurement = new Measurement();
        
        $endless = true;
        while ($endless) {
            $time = time();
            $newtime = $time;
            while ($time + MainLoop::checkInterval >= $newtime) { 
                $newtime = time();
                $this->relaisController->checkStatus();
                // measure 
                $this->measure();
                // check incoming messages
                $this->socket->check();
                while ($newtime == time()) { // wait for second to finish
                    $this->socket->check();
                    usleep(100000);
                }
            }
            
            if($this->_save_to_db){
                $this->saveDayMeasurements();
            
                $this->monthIterator++;

                if($this->monthIterator >= MainLoop::monthMultiplicator){
                    $this->yearIterator++;
                    $this->monthIterator = 0;
                    $this->saveMonthMeasurements();
                }
                if($this->yearIterator >= MainLoop::yearMultiplicator){
                    $this->yearIterator = 0;
                    $this->saveYearMeasurements();
                }
            }
            
            
        }
    }
    /**
     * Read the values of all 4 devices
     */
    private function measure() {
        for ($i = 0; $i < 4; $i++) {
            if($this->_generate_testdata){
                $values = array();
                $values["voltage"] = 230;
                // (sin(t) + sin(5t) + 2) * 1000 + rand(0 bis 300)
                $values["current"] = intval((sin(time()/100.0) + sin(time()/20.0) +2.0)*1000.0) + rand(0,300);
                $values["active"] = $values["voltage"] * $values["current"] / 1000;
                $values["reactive"] = rand(0,100);
                $values["apparent"] = sqrt($values["active"] + $values["reactive"]);
            }else{
                $values = $this->measurement->readAll($i);
            }

            if($values){
                $this->devices[$i]->add($values);
            }
        }
    }

    private function saveDayMeasurements(){
        for ($device = 0; $device < 4; $device++) {
            if ($this->devices[$device]->changed()) {
                 $this->devices[$device]->normalize();
                $active = $this->devices[$device]->getActivePower(false);
                $reactive = $this->devices[$device]->getReactivePower(false);
                $active_min = $this->devices[$device]->getActivePowerMin();
                $active_max = $this->devices[$device]->getActivePowerMax();
                $reactive_min = $this->devices[$device]->getReactivePowerMin();
                $reactive_max = $this->devices[$device]->getReactivePowerMin();
                
                DBManager::writeMeasurement(DBManager::DB_DAY_MEASUREMENTS, $device, 
                        $active, $active_min, $active_max, $reactive, $reactive_min, $reactive_max);
                Log::notice($this, "Saved values to db DayMeasurements \n");
            //    echo "saved value \n";
            }else{
                Log::notice($this, "Not saved because in tolerance\n");
            }
        }
    }
    private function saveMonthMeasurements(){
        for ($device = 0; $device < 4; $device++) {
            //3600
            $stats = DBManager::getStatistics(DBManager::DB_DAY_MEASUREMENTS, $device, 
                    "strftime('%s', 'now')-".MainLoop::checkInterval * MainLoop::monthMultiplicator, "strftime('%s', 'now')");
            if(count($stats) < 6 || 
                    $stats[0]===null || $stats[1]===null || $stats[2]===null || $stats[3]===null || $stats[4]===null || $stats[5]===null){
                Log::warning($this, "Statistics of day measurements are invalid: ".  var_export($stats, true));
            }else{
                DBManager::writeMeasurement(DBManager::DB_MONTH_MEASUREMENTS, $device, 
                        $stats[0], $stats[1], $stats[2], $stats[3], $stats[4], $stats[5]);
                Log::notice($this, "Saved values to db MonthMeasurements \n");
            }
        }
    }
    private function saveYearMeasurements(){
        for ($device = 0; $device < 4; $device++) {
            //86400
            $stats = DBManager::getStatistics(DBManager::DB_MONTH_MEASUREMENTS, $device, 
                    "strftime('%s', 'now')-".MainLoop::checkInterval * MainLoop::monthMultiplicator * MainLoop::yearMultiplicator, "strftime('%s', 'now')");
            if(count($stats) < 6 || 
                    $stats[0]===null || $stats[1]===null || $stats[2]===null || $stats[3]===null || $stats[4]===null || $stats[5]===null){
                Log::warning($this, "Statistics of month measurements are invalid: ".  var_export($stats, true));
            }else{
                DBManager::writeMeasurement(DBManager::DB_YEAR_MEASUREMENTS, $device, 
                        $stats[0], $stats[1], $stats[2], $stats[3], $stats[4], $stats[5]);
                Log::notice($this, "Saved values to db YearMeasurements \n");
            }
        }
    }
    /**
     * Get the measurements of a device 
     * @param integer $device
     * @return type MeasureDevice
     */
    public function getDevice($device) {
        return $this->devices[$device];
    }

}
