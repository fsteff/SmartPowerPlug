<?php

/**
 * Class to handle the SQLite3 Database
 *
 * @author Stefan Fixl
 */
class DBManager {
    /**
     * Connect to the database
     * @return \SQLite3 connection
     */
    private static function connect(){
        $connection = new SQLite3("smartplug.sqlite");
        return $connection;
    }
    /**
     * Disconnect the database
     * @param SQLite $connection
     */
    private static function disconnect($connection){
        $connection->close();
    }

    /**
     * Read from the database
     * @param string $database database name
     * @param string $start start time(timestamp  - use with "srftime(...)"
     * @param string $end end time (timestamp -//-)
     * @param string $additional additional query
     * @param string $selector selector for the query
     * @return array|null
     */
    public static function read($database, $start, $end, $additional = "", $selector = "*"){
        $query = "SELECT $selector FROM $database WHERE (datetime > $start) AND (datetime < $end) $additional ;";
        //echo $query; 
        $connection = DBManager::connect(); 
        
        $result = $connection->query($query);
        //echo $connection->lastErrorMsg() ." at query '$query'" ."<br>";
        $i = 0;
        while(($code = $connection->lastErrorCode()) == 5 && $i < 5){
            usleep(250000);
            $result = $connection->query($query);
            $i++;
        }
        if(! $result){           
            echo("Error with query '$query' :" . $connection->lastErrorMsg());
            DBManager::disconnect($connection);
            return null;
        }

        $ret = array();
        while(($tmp = $result->fetchArray())!= false){
            $ret[] = $tmp;
        }
        
        DBManager::disconnect($connection);
        return $ret; 
    }
    /**
     * Read from a device
     *@param string $database database name
     * @param string $start start time(timestamp  - use with "srftime(...)"
     * @param string $end end time (timestamp -//-)
     * @param string|int $device device to use
     * @param string $selector selector for the query
     * @return array|null
     */
    public static function readDevice($database, $start, $end, $device, $selector = "*"){ 
        return DBManager::read($database, $start, $end, "AND (device=$device)", $selector);
    }
    /**
     * Get sqlite3-valid a string that represents the actual time
     * @return string
     */
    public static function now(){
        return "strftime('%s', 'now')";
    }
    /**
     * Get a sqlite3-valid string that represents a date
     * See https://www.sqlite.org/lang_datefunc.html for further details
     * @param string $date
     * @return string
     */
    public static function date($date){
        return "strftime('%s', '$date')";
    }
    /**
     * Get a sqlite3-valid string that represents a date in seconds from now
     * @param string $seconds
     * @return string
     */
    public static function secondsBeforeNow($seconds){
        return "(strftime('%s', 'now') - $seconds)";
    }
        
}

