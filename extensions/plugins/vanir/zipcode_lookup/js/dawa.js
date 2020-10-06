var dawa = {
	lookup: function (zipcode)
	{
		var settings = {
			url: 'https://dawa.aws.dk/postnumre/' + zipcode,
			type: 'get',
			data: {},
			dataType: 'json',
			async: false
		};

		var ajax = jQuery.ajax(settings);

		var response = ajax.responseJSON;

		if ('type' in response)
		{
			throw new Error(response.type)
		}

		return response;
	},

	getCity: function (zipcode)
	{
		var response = dawa.lookup(zipcode);

		return response.navn;
	}
}
