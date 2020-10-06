jQuery(document).ready(function () {
	var eanInput   = jQuery('#registerForm').find('#jform_ean');
	var eanField   = eanInput.closest('.custom-field-ean');
	var isRequired = eanInput.attr('required') === 'required';

	if (isRequired)
	{
		eanInput.removeClass('required');
		eanInput.removeAttr('required');
		eanInput.removeAttr('aria-required');
	}

	eanField.hide();

	jQuery('#registerForm').on('click', 'input[type=radio][name=jform\\[register_type\\]]', function (){
		var input = jQuery(this);

		var label = input.next('label[for='+ input.attr('id') +']');

		if (label.text().trim() == 'EAN')
		{
			/*
			Changes the value of the radio button from ean to business
			when it's clicked so we post the right value to the controller
			but keep the ean value if the page is refreshed to avoid visual bugs
			 */
			input.val('business');

			if (isRequired)
			{
				eanInput.addClass('required');
				eanInput.attr('aria-required', true);
				eanInput.attr('required', true);
			}

			// Shows the field
			eanField.show();
		}
		else
		{
			if (isRequired)
			{
				eanInput.removeClass('required');
				eanInput.removeAttr('aria-required');
				eanInput.removeAttr('required');
			}

			// Hides the field
			eanField.hide();
		}
	});
});
