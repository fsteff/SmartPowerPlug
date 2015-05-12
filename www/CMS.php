<?php

/**
 * Builds the main parts of the page
 *
 * @author Stefan Fixl
 */
class CMS {
    /**
     * Starts the page with a <head> and a <body> tag and and includes the 
     * CSS stylesheets and the Javascript scripts
     * @param boolean $inclchart whether to include the rickshaw library or not
     * @param string $pagename the name/title of the page
     */
    public static function startPage($inclchart = true, $pagename = "SmartPlug"){
        ?>
        <!DOCTYPE html>
            <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <title><?php echo $pagename;?></title>
                <meta name="<?php echo $pagename;?>" content="">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                
                <link rel="stylesheet" href="css/rickshaw.min.css"> 
                <link rel="stylesheet" href="css/jquery-ui.css">
                <link rel="stylesheet" href="css/styles.css"> 
                <link rel="stylesheet" href="css/menumaker.css">
                
                <script src="js/jquery-2.1.3.min.js"></script>
                <script src="js/jquery-ui.min.js"></script>
                <script src="js/menumaker.js"></script>
                <?php
                if($inclchart){
                    echo '<script src="js/d3.min.js"></script>
                    <script src="js/d3.layout.min.js"></script>
                    <script src="js/rickshaw.js"></script>
                    <script src="js/MyOwnTimeFixture.js"></script>
                    <script src="js/rickshawMyAjax.js"></script>';
                }
                ?>
                <script src="js/scripts.js"></script>
            </head>
            <body>
<?php
    }
    /**
     * Insert the navigation bar
     * @param int $device the device that is actually shown
     * @param string $db the name of the database that is actually shown
     */
    public static function insertMenu($device = 0, $db = "DayMeasurements"){
        ?>
            <div id='cssmenu'>
                <ul>
                    <li><a href="#">Geräte</a>
                        <ul>
                            <li><a href="index.php?<?php echo "db=$db&device=0"?>">Gerät 1</a></li>
                            <li><a href="index.php?<?php echo "db=$db&device=1"?>">Gerät 2</a></li>
                            <li><a href="index.php?<?php echo "db=$db&device=2"?>">Gerät 3</a></li>
                            <li><a href="index.php?<?php echo "db=$db&device=3"?>">Gerät 4</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Zeitraum</a>
                        <ul>
                            <li><a href="index.php?db=DayMeasurements<?php echo "&device=$device";?>">Tages-Messwerte</a></li>
                            <li><a href="index.php?db=MonthMeasurements<?php echo "&device=$device";?>">Monats-Messwerte</a></li>
                            <li><a href="index.php?db=YearMeasurements<?php echo "&device=$device";?>">Jahres-Messwerte</a></li>
                        </ul>
                    </li>
                    
                    <li><a href="settings.php">Einstellungen</a>
                </ul>
            </div>        
        <?php
    }
    
    /**
     * End the page with a </body> tag
     */
    public static function endPage(){
        ?>
                
            </body> 
            
        <?php
    }
}
