<?php use Joomla\CMS\Language\Text; ?>
<input type="text" id="lookup-ean-input" name="ean-lookup-input" placeholder="<?php echo Text::_('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_EAN_NUMBER'); ?>"/>
<button type="button" id="lookup-ean-button"><?php echo Text::_('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_SEARCH_BUTTON'); ?></button>
<script>
	(function($) {
		$(document).ready(function() {
			var eanInput  = $('#lookup-ean-input');
			var eanButton = $('#lookup-ean-button');

			eanInput.keydown(function(e) {
				if (13 === e.keyCode)
				{
					e.preventDefault();
					eanButton.trigger('click');
				}
			});

			eanInput.hide();
			eanButton.hide();

			$('#registerForm').on(
				'click',
				'input[name="jform\\[register_type\\]"]',
				{input: eanInput, button: eanButton},
				function(event) {
					var type = $(this);
					var label = type.next('label[for='+ type.attr('id') +']');

					if (label.text().trim() === 'EAN')
					{
						event.data.input.show();
						event.data.button.show();
					}
					else
					{
						event.data.input.hide();
						event.data.button.hide();
					}
			});

			eanButton.on('click', {input: eanInput}, function(event) {
				$('#lookup-vat-input').val('');

				event.preventDefault();

				var ean = event.data.input.val();

				/**
				 * @var   data
				 *
				 * @property vat
				 * @property name
				 * @property address
				 * @property city
				 * @property zipcode
				 * @property phone
				 * @property email
				 * @property ean
				 */
				var data = redSHOPB.lookup.ean(ean);

				if ('error' in data)
				{
					return false;
				}

				fillRegisterForm(data);
			});
		});
	})(jQuery);
</script>
