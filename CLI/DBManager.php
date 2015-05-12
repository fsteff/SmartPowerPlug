<?php

/**
 * Class to handle SQLite Database
 *
 * @author Stefan Fixl
 */
class DBManager {
    /**
     * data base name for day measurements
     */
    const DB_DAY_MEASUREMENTS = "DayMeasurements";
    /**
     * data base name for month measurements
     */
    const DB_MONTH_MEASUREMENTS = "MonthMeasurements";
    /**
     * data base name for year measurements
     */
    const DB_YEAR_MEASUREMENTS = "YearMeasurements";
    /**
     * The database filename
     */
    const DB_FILE = "smartplug.sqlite";
    /**
     * Connect to the database
     * @return \SQLite3 connection
     */
    private static function connect(){
        $connection = new SQLite3(DBManager::DB_FILE);
        return $connection;
    }
    /**
     * Disconnect the database
     * @param SQLite3 $connection
     */
    private static function disconnect($connection){
        $connection->close();
    }
    /**
     * Write a new measurement to a database 
     * @param String $database (either DayMeasurements, MonthMeasurements or YearMeasurements)
     * @param int $device
     * @param float $active
     * @param float $activeMin
     * @param float $activeMax
     * @param float $reactive
     * @param float $reactiveMin
     * @param float $reactiveMax
     */
    public static function writeMeasurement($database, $device, $active, $activeMin, $activeMax, $reactive, $reactiveMin, $reactiveMax){
        $query = "INSERT INTO $database (device, datetime, active, activeMin, activeMax, reactive, reactiveMin, reactiveMax) "
            . "VALUES($device, strftime('%s', 'now'), $active, $activeMin, $activeMax, $reactive, $reactiveMin, $reactiveMax)";
        $connection = DBManager::connect();       
        if(! DBManager::query($connection, $query)){
            Log::critical(__CLASS__, "Error with query '$query' :" . $connection->lastErrorMsg());
        }
        DBManager::disconnect($connection); 
    }
    
    /**
     * Returns the average, mininum and maximum values of the saved values 
     * @param String $database
     * @param int $device
     * @param string $starttime (use like this: "strftime('%s', 'now') - [somevalue]")
     * @param string $endtime ((use like this: "strftime('%s', 'now') - [somevalue]")
     * @return array(average active, minimum active, maximum active, average reactive, minimum reactive, maximum reactive)
     */
    public static function getStatistics($database, $device, $starttime, $endtime = "strftime('%s', 'now')"){
        $query = "SELECT AVG(active) as active, MIN(activeMin) as activeMin, MAX(activeMax) as activeMax,"
                . " AVG(reactive) as reactive, MIN(reactiveMin) as reactiveMin, MAX(reactiveMax) as reactiveMax FROM $database WHERE"
                . "(device = $device) AND (datetime > $starttime) AND (datetime < $endtime);";
        $connection = DBManager::connect();       
        $result = DBManager::query($connection, $query);
        if(! $result){
            Log::critical(__CLASS__, "Error with query '$query' :" . $connection->lastErrorMsg());
            DBManager::disconnect($connection);
            return null;
        }
        $ret = $result->fetchArray();
        
        DBManager::disconnect($connection);
        return $ret; 
    }
    /**
     * Check if the databases exist and create them if it is not the case.
     */
    public static function checkDBs(){
        DBManager::createDB(DBManager::DB_DAY_MEASUREMENTS);
        DBManager::createDB(DBManager::DB_MONTH_MEASUREMENTS);
        DBManager::createDB(DBManager::DB_YEAR_MEASUREMENTS);
    }
    /**
     * Check if the database exit and create it it it does not exist.
     * @param String $dbname
     */
    public static function createDB($dbname){
        $query = "select * from $dbname limit 1;";
        $connection = DBManager::connect();       
        $result = $connection->query($query);
        if(! $result){
            $query = "CREATE TABLE $dbname(device INTEGER, "
                    . "datetime INTEGER DEFAULT CURRENT_TIMESTAMP, "
                    . "active FLOAT, "
                    . "activeMin FLOAT, "
                    . "activeMax FLOAT, "
                    . "reactive FLOAT, "
                    . "reactiveMin FLOAT, "
                    . "reactiveMax FLOAT, "
                    . "PRIMARY KEY(device, datetime ASC));";
            $result = $connection->query($query);
        }
    }
    /**
     * Execute a query and retry if the database is locked
     * @param Sqlite3 $connection
     * @param String $query
     * @return resource result
     */
    private static function query($connection, $query){
        // suppress error reports - the "database is locked" warning happens pretty often
        $result = @$connection->query($query);
        $i = 0;
        while($connection->lastErrorCode() == 5 && $i < 5){
            Log::notice(__CLASS__, "Database is locked - retry in 250ms");
            usleep(250000);
            $result = @$connection->query($query);
            $i++;
        }
        if($i > 4 && $connection->lastErrorCode() == 5){
            Log::warning(__CLASS__, "Database is locked - tried already 5 times");
        }
        return $result;
    }

}
