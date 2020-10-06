/**
 * Fills out the registration form with data from redSHOPB.lookup
 *
 * @param   {PlainObject}   data   Data returned from the {redSHOPB.lookup.vat} and {redSHOPB.lookup.ean} methods
 *
 * @return   {Boolean}
 */
function fillRegisterForm(data)
{
	if (null == data)
	{
		return false;
	}

	var form = jQuery('#registerForm');

	form.find('#jform_business_company_name').val(data.name);
	form.find('#jform_vat_number').val(data.vat);
	form.find('#jform_ean').val(data.ean);
	form.find('#jform_invoice_email').val(data.email);
	form.find('#jform_billing_address').val(data.address);
	form.find('#jform_billing_city').val(data.city);
	form.find('#jform_billing_zip').val(data.zipcode);
	form.find('#jform_billing_phone').val(data.phone);

	return true;
}
