<?php use Joomla\CMS\Language\Text; ?>
<input type="text" id="lookup-vat-input" name="vat-lookup-input" placeholder="<?php echo Text::_('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_VAT_NUMBER'); ?>"/>
<button type="button" id="lookup-vat-button"><?php echo Text::_('PLG_VANIR_LOOKUP_INTEGRATION_LOOKUP_SEARCH_BUTTON'); ?></button>
<script>
	(function($) {
		$(document).ready(function() {
			var vatInput  = $('#lookup-vat-input');
			var vatButton = $('#lookup-vat-button');

			vatInput.keydown(function(e) {
				if (13 === e.keyCode)
				{
					e.preventDefault();
					vatButton.trigger('click');
				}
			});

			vatButton.on('click', {input: vatInput}, function(event) {
				$('#lookup-ean-input').val('');

				event.preventDefault();

				var vat = event.data.input.val();

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
				 */
				var data = redSHOPB.lookup.vat(vat);

				if ('error' in data)
				{
					return false;
				}

				fillRegisterForm(data);
			});
		});
	})(jQuery);
</script>
