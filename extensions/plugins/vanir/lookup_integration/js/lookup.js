/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
var redSHOPB = window.redSHOPB || {};

/**
 * Object for handling lookups related to user/company registration
 *
 * @type   {Object}
 */
redSHOPB.lookup = {

	/**
	 * Looks up the supplied VAT number and returns a JSON object
	 *
	 * @param   {String}   vat        VAT number to look up
	 * @param   {String}   country    Country to check in (Alpha2 format)
	 *                                Defaults to 'dk'
	 *
	 * @return   {PlainObject}
	 */
	vat: function (vat, country)
	{
			country = country || 'dk';

			var settings = {
				dataType: "json",
				type: 'get',
				url: '//cvrapi.dk/api?search='+ vat +'&country='+ country.toLowerCase(),
				async: false
			};

			var response = jQuery.ajax(settings);

			/** global: Joomla */
			if (typeof response.responseJSON === 'undefined')
			{
				redSHOPB.messages.displayMessage(Joomla.JText._('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_CONNECTION_FAILED'), 'alert-error');

				return {
					error: Joomla.JText._('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_CONNECTION_FAILED')
				};
			}
			else if (200 === response.status && !response.responseJSON.hasOwnProperty('error'))
			{
				redSHOPB.messages.displayMessage(Joomla.JText._('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_NUMBER_FOUND'), 'alert-success');
			}
			else
			{
				redSHOPB.messages.displayMessage(Joomla.JText._('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_NUMBER_NOT_FOUND'), 'alert-error');
			}

			return response.responseJSON;
	},

	/**
	 * Looks up the supplied EAN number and then calls {redSHOPB.lookup.vat} to get the company info
	 *
	 * @param   {String}   ean    EAN number to look up
	 *
	 * @return   {PlainObject}
	 */
	ean: function (ean)
	{
		try
		{
			var settings = {
				dataType: 'json',
				url: 'index.php?option=com_ajax&plugin=lookup_integration&group=vanir&method=post&format=json',
				type: 'post',
				data: {
					ean: ean,
				},
				async: false
			};

			var response = jQuery.ajax(settings);
			var xml      = jQuery(jQuery.parseXML(response.responseJSON.xml));
			var vat      = xml.find('ParticipantInfoDTO > Participant > UnitCVR').text();
			var data     = redSHOPB.lookup.vat(vat);
			data.ean     = ean;

			redSHOPB.messages.displayMessage(Joomla.JText._('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_EAN_NUMBER_FOUND'), 'alert-success');
		}
		catch (e)
		{
			var data = {};
			data.error = e.message;

			redSHOPB.messages.displayMessage(Joomla.JText._('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_EAN_NUMBER_NOT_FOUND'), 'alert-error');
		}

		return data;
	}
}
