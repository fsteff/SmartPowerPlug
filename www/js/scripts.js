var graph;
/**
 * Builds the graph functionality after receiving the json-formatted data by the ajax request 
 * @param rickshawMyAjax myajax
 */
function onComplete(myajax){
    var graph = myajax.graph;
    var x_axis = new Rickshaw.Graph.Axis.Time({
        graph: graph,
        //timeFixture: new Rickshaw.Fixtures.Time()
        timeFixture: new MyOwnTimeFixture()
    });

    var y_axis = new Rickshaw.Graph.Axis.Y({
        graph: graph,
        orientation: 'left',
        tickFormat: Rickshaw.Fixtures.Number.formatKMBT,
        element: document.getElementById('y_axis')
    });

    var legend = new Rickshaw.Graph.Legend({
        element: document.querySelector('#legend'),
        graph: graph
    });

    var hoverDetail = new Rickshaw.Graph.HoverDetail({
        graph: graph

    });
    var shelving = new Rickshaw.Graph.Behavior.Series.Toggle({
        graph: graph,
        legend: legend
    });
    graph.render();
    
    var slider = new Rickshaw.Graph.RangeSlider({
        graph: graph,
        element: document.querySelector('#slider')
    });
    
}
/**
 * Builds the graph
 * It is called by the index.php site.
 * @param string dataURL
 * @param string database
 * @param string start
 * @param string end
 * @param string device
 * @returns nothing
 */
function loadGraph(dataURL, database, start, end, device) {
    var palette = new Rickshaw.Color.Palette();
    graph = new RickshawMyAjax({
        element: document.querySelector("#chart"),
        width: $(document).width()-60,
        height: $(document).height()-300,
        dataURL: dataURL,
        postData: {db: database, start:start,end:end, device:device},
        onData: function(data){$.each(data, function(index) {
                data[index].color = palette.color();
            });
            return data;
        },
        onComplete: onComplete,
        renderer: 'line',
        interpolation: 'linear'
      
    });
    /**
     * If auto-update is checked, call graph.update every 3 seconds
     */
    setInterval(function(){
        autoUpdate = $("#autoUpdate").is(":checked");
        if(autoUpdate === true){
            graph.update();
        } 
    }, 3000);
}


