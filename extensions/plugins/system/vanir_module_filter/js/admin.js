(function ($) {
	$(document).ready(function(){

		/**
		 * Hide fields through javascript
		 *
		 * @param   string  mode          Speficic selector of fields to hide
		 * @param   string  baseSelector  Prefix for selector
		 *
		 * @return  void
		 */
		function hideVanirFields(mode, baseSelector) {
			$(baseSelector + '-' + mode).closest('.control-group').fadeOut();
		}

		/**
		 * Show fields through javascript
		 *
		 * @param   string  mode          Speficic selector of fields to show
		 * @param   string  baseSelector  Prefix for selector
		 *
		 * @return  void
		 */
		function showVanirFields(mode, baseSelector) {
			$(baseSelector + '-' + mode).closest('.control-group').fadeIn();
		}

		// Mode selector
		$('#jform_params_vanir_show_mode').change(function(){
			var activeMode = $(this).val();
			var baseSelector = '.js-vanir-show-mode';

			switch (activeMode) {
				case '1':
					hideVanirFields('2', baseSelector);
					showVanirFields('1', baseSelector);
					break;
				default:
					hideVanirFields('1', baseSelector);
					showVanirFields('2', baseSelector);
					break;
			}
			$('#jform_params_vanir_show_in_category_mode').trigger('change');
			$('#jform_params_vanir_show_in_item_mode').trigger('change');
		}).trigger('change');

		// Category mode selector
		$('#jform_params_vanir_show_in_category_mode').change(function(){
			var activeMode = $(this).val();
			var baseSelector = '.js-vanir-show-in-category-mode';

			// If fixed mode is enabled hide all the options
			if ($('#jform_params_vanir_show_mode').val() === '1') {
				hideVanirFields('1', baseSelector);
				hideVanirFields('2', baseSelector);
				hideVanirFields('3', baseSelector);
				hideVanirFields('4', baseSelector);

				return;
			}

			switch (activeMode) {
				case '1':
					hideVanirFields('2', baseSelector);
					hideVanirFields('3', baseSelector);
					hideVanirFields('4', baseSelector);
					showVanirFields('1', baseSelector);
					break;
				case '2':
					hideVanirFields('1', baseSelector);
					hideVanirFields('3', baseSelector);
					hideVanirFields('4', baseSelector);
					showVanirFields('2', baseSelector);
					break;
				case '3':
					hideVanirFields('1', baseSelector);
					hideVanirFields('2', baseSelector);
					hideVanirFields('4', baseSelector);
					showVanirFields('3', baseSelector);
					break;
				default:
					hideVanirFields('1', baseSelector);
					hideVanirFields('2', baseSelector);
					hideVanirFields('3', baseSelector);
					showVanirFields('4', baseSelector);
					break;
			}

		}).trigger('change');

		// Item mode selector
		$('#jform_params_vanir_show_in_item_mode').change(function(){
			var activeMode = $(this).val();
			var baseSelector = '.js-vanir-show-in-item-mode';

			// If fixed mode is enabled hide all the options
			if ($('#jform_params_vanir_show_mode').val() === '1') {
				hideVanirFields('1', baseSelector);
				hideVanirFields('2', baseSelector);
				hideVanirFields('3', baseSelector);
				hideVanirFields('4', baseSelector);

				return;
			}

			switch (activeMode) {
				case '1':
					hideVanirFields('2', baseSelector);
					hideVanirFields('3', baseSelector);
					hideVanirFields('4', baseSelector);
					showVanirFields('1', baseSelector);
					break;
				case '2':
					hideVanirFields('1', baseSelector);
					hideVanirFields('3', baseSelector);
					hideVanirFields('4', baseSelector);
					showVanirFields('2', baseSelector);
					break;
				case '3':
					hideVanirFields('1', baseSelector);
					hideVanirFields('2', baseSelector);
					hideVanirFields('4', baseSelector);
					showVanirFields('3', baseSelector);
					break;
				default:
					hideVanirFields('1', baseSelector);
					hideVanirFields('2', baseSelector);
					hideVanirFields('3', baseSelector);
					showVanirFields('4', baseSelector);
					break;
			}
		}).trigger('change');
	});
})(jQuery);
