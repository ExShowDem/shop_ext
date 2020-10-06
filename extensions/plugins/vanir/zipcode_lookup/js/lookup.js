jQuery(document).ready(function () {
	/**
	 * Fills out the supplied field with a city based on the zipcode entered
	 *
	 * @param   {Event}   event   Event trigger on keyup
	 *
	 * @return   {String}
	 */
	function fillCityField(event)
	{
		var zipcode = jQuery(event.target).val();

		if (zipcode.length != 4)
		{
			return false;
		}

		try
		{
			/** global: dawa */
			var city = dawa.getCity(zipcode);

			jQuery(event.data.input).val(city);
		}
		catch (e)
		{
			var msg = e.message;

			if ( ! (msg === 'ResourcePathFormatError' || msg === 'ResourceNotFoundError'))
			{
				throw e;
			}

			return false;
		}

		return true;
	}

	// Register event handlers for the zip code fields on the registration page
	jQuery('#registerForm').on('keyup', '#jform_billing_zip', {input: '#jform_billing_city'}, fillCityField);
	jQuery('#registerForm').on('keyup', '#jform_shipping_zip', {input: '#jform_shipping_city'}, fillCityField);
	jQuery('#guestCheckoutForm').on('keyup', '#zip', {input: '#city'}, fillCityField);
});
