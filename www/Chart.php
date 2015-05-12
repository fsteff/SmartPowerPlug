<?php

/**
 * Builds and initalizes a rickshaw chart
 * Use only one chart per page!
 *
 * @author Stefan Fixl
 */
class Chart {
    /**
     * Insert the html tags the chart needs
     */
    public static function htmlChart() {
        ?>
        <form>
            <p><input type="checkbox" id="autoUpdate">auto update</p>
        </form>
        <div id="y_axis"></div>
        <div id="chart"></div>
        <div id="legend"></div>
        <div id="slider">
            <p id="sliderval_right"></p>
            <p id="sliderval_left"></p>
        </div>
        
    <!--    <form id="side_panel">
            <div id="slider"></div>
            <div id="smoother"></div>
        </form>  -->
        <?php
    }
    /**
     * Add the script that initializes the chart
     * @param string $database db name
     * @param string $start start time
     * @param string $end end time
     * @param string $device device to view
     */
    public static function initChart($database, $start, $end, $device) {
     
        ?>
        <script>
            loadGraph("data.php", <?php echo "\"$database\", \"$start\", \"$end\", \"$device\""?>);
        </script>

        <?php
    }

}
