/**
 * Own Implementation of the Rickshaw-Ajax functionality.
 * This also supports auto-updating the graph
 */

RickshawMyAjax = Rickshaw.Class.create( {
	initialize: function(args) {
		this.dataURL = args.dataURL;
                //von mir:
                this.postData = args.postData;

		this.onData = args.onData || function(d) { return d; };
		this.onComplete = args.onComplete || function() {};
		this.onError = args.onError || function() {};

		this.args = args; // pass through to Rickshaw.Graph

		this.request();
	},
       

	request: function() {
                // von mir verändert
		jQuery.ajax( {
			url: this.dataURL,
                        data: this.postData,
                        //processData: false,
                        type: "GET",
                        async: true,
			dataType: 'json',
			success: this.success.bind(this),
			error: this.error.bind(this)
		} );
	},

	error: function() {

		console.log("error loading dataURL: " + this.dataURL);
		this.onError(this);
	},

	success: function(data, status) {

		data = this.onData(data);
		this.args.series = this._splice({ data: data, series: this.args.series });

		this.graph = this.graph || new Rickshaw.Graph(this.args);
		this.graph.render();

		this.onComplete(this);
	},

	_splice: function(args) {

		var data = args.data;
		var series = args.series;

		if (!args.series) return data;

		series.forEach( function(s) {

			var seriesKey = s.key || s.name;
			if (!seriesKey) throw "series needs a key or a name";

			data.forEach( function(d) {

				var dataKey = d.key || d.name;
				if (!dataKey) throw "data needs a key or a name";

				if (seriesKey == dataKey) {
					var properties = ['color', 'name', 'data'];
					properties.forEach( function(p) {
						if (d[p]) s[p] = d[p];
					} );
				}
			} );
		} );

		return series;
	},
        successUpdate: function(data){
                this.args.series = this._splice({ data: data, series: this.args.series });
                //this.graph.series = series;
                this.graph.update();
        },
        
        update: function() {
        jQuery.ajax({
            url: this.dataURL,
            data: this.postData,
            type: "GET",
            async: true,
            dataType: 'json',
            success: this.successUpdate.bind(this),
            error: this.error.bind(this)
        });
        

        
    }
} );


