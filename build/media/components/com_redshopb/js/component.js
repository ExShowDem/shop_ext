var SqueezeBox =
{
	close: function(e) {
		jQuery('.modal').modal('hide');
	},
    initialize: function(presets) {
        return this;
    },
    assign: function(to, options) {
    }
}

function parseFloatOpts (str) {
	str = String(str);
	var ar = str.split(/\.|,/);

	var value = '';
	for (var i in ar) {
		if( ar.hasOwnProperty( i ) ) {
			if (i>0 && i==ar.length-1) {
				value += ".";
			}
			value +=ar[i];
		}
	}

	return Number(value);
}
