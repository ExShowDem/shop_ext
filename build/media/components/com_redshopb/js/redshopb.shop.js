/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

jQuery(document).ready(function(){
    var $productList = jQuery('#pageProductList');
	if ($productList.length) {
		var data = {
			"html": $productList.html(),
			"productsCount": jQuery('.redshopb-productlist-count').html()
		};
		var $filterWrapper = jQuery('.mod_redshopb_filter');
		if ($filterWrapper.length){
		    data['filterWrapper'] = '.mod_redshopb_filter';
		    data['moduleHtml'] = $filterWrapper.html();
        }
		window.history.replaceState(data, "", window.location.href);
		window.onpopstate = function (e) {
			if (e.state) {
				redSHOPB.shop.applyContent(e.state);
			}
		};
		var $catLayouts = jQuery('.shop-category-icon');
		$catLayouts.find('a').attr('href', '#')
			.on('click', function(e){
				e.preventDefault();
				var $item = jQuery(this);
				if ($item.hasClass('show-as-active')){
					return;
				}
				var $parent = $item.parent('.shop-category-icon');
				var parentClasses = $parent.attr('class');
				var matches = parentClasses.split(' ');
				var selectedLayout = 'list';
				matches.forEach(function(item) {
					var splitItem = item.split('-');
					if (splitItem.length == 4){
						selectedLayout = splitItem[3];
						return true;
					}
				});
				var form = jQuery('#pageProductList').parents('form');
				form.find('input[name="show_as"]').val(selectedLayout);
				form.find('input[name="page"]').remove();
				var settings = {
					url: form.attr('action'),
					type:'POST',
					dataType: 'json',
					data: redSHOPB.form.getData(form, 'shop.ajaxGetProductListPage'),
					beforeSend: function() {
						$productList.addClass('opacity-40');
						$catLayouts.find('a').removeClass();
					}
				};
				jQuery.ajax(settings)
					.done(function(data) {
						redSHOPB.shop.applyContent(data);
						$catLayouts.filter('.shop-category-icon-'+selectedLayout).find('a').addClass('show-as-active category-'+selectedLayout+'-active');
					})
					.fail(function(jqXHR) {
						data = jqXHR.responseJSON;
						redSHOPB.messages.displayMessage(data.message, data.messageType, '');
					})
					.always(function() {
						$productList.removeClass('opacity-40');
					});
			});
	}
});

redSHOPB.shop = {

    url: null,
    image: null,
    loadingText: null,

    productPrices: [],
    productIds: [],
    productItemIds: [],
    collectionIds: [],
    quantities: [],
    qtyChangeTimeout: null,

    productPrice: null,

    init:function(url, image, loadingText)
    {
        redSHOPB.shop.url = url;
        redSHOPB.shop.image = image;
        redSHOPB.shop.loadingText = loadingText;

        var productListQuantities = jQuery('input.quantityForOneProduct');
        productListQuantities.attr('data-volume_pricing_prefix', '.js-volume_pricing');
        productListQuantities.on('change', function(event){
            if (redSHOPB.shop.qtyChangeTimeout)
            {
                clearTimeout(redSHOPB.shop.qtyChangeTimeout);
            }

            redSHOPB.shop.qtyChangeTimeout = setTimeout(function () {
                redSHOPB.shop.loadVolumePrice(event);
                redSHOPB.shop.productlist.updateCount();
                redSHOPB.shop.qtyChangeTimeout = null;
            }, 1000);
        });
    },

    applyContent: function(data){
		if(data.filterWrapper != undefined && data.moduleHtml != undefined && data.moduleHtml != '') {
			jQuery(data.filterWrapper).html(data.moduleHtml).find('select').chosen({
				"disable_search_threshold": 10,
				"allow_single_deselect": true
			});
		}
		var wrapper = jQuery('#pageProductList');
		var listCount = jQuery('.redshopb-productlist-count');
		redSHOPB.ajax.updateContent(wrapper, data.html);
		listCount.html(data.productsCount);
		redSHOPB.shop.assignAddToFavoriteButtons();
		wrapper.find('select').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
		var productListForm = wrapper.closest('form');
		if (productListForm.length > 0 && data.numberOfPages != undefined){
			productListForm.find('input[name="noPages"]').val(data.numberOfPages);
		}
		var tabUL = wrapper.find('#collectionTabs');
		var tabs  = wrapper.find('.tab-pane[id^="collection_"]');
		if (tabs.length > 0){
			tabs.each(function (index, element) {
				element = jQuery(element);
				var classes = '';
				if (element.hasClass('active')) {
					classes = 'active';
				}
				var tabMarkup = jQuery('<li class="' + classes + '"><a href="#' + element.attr('id') + '" data-toggle="tab">' + element.attr('data-title') + '</a></li>');
				tabUL.append(tabMarkup);
			});
        }
		wrapper.trigger('change');
    },

    loadVolumePrice:function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var productId = parseInt(targ.attr('data-product_id'));
        var quantity = parseInt(targ.val());
        var prefix = targ.attr('data-volume_pricing_prefix');

        var selector = prefix + '[data-product_id="' + productId +'"]';
        var priceObject = jQuery(selector);

        // We only stop propagation if this is price volume object.

        if (priceObject.prop('tagName') != undefined)
        {
            event.stopPropagation();
            priceObject.attr('data-quantity', quantity);
            redSHOPB.shop.loadPrices(selector, 'shop.ajaxUpdatePrices');
        }
    },

    loadPrices:function(selector, task)
    {
        // Reset the object variables
        redSHOPB.shop.productIds = [];
        redSHOPB.shop.quantities = [];
        redSHOPB.shop.collectionIds = [];
        redSHOPB.shop.productPrice = jQuery(selector);

        redSHOPB.shop.productPrice.each(
            function(index, element)
            {
                element = jQuery(element);

                redSHOPB.shop.productIds.push(parseInt(element.attr('data-product_id')));
                redSHOPB.shop.collectionIds.push(parseInt(element.attr('data-collection_id')));

                var quantity = element.attr('data-quantity');

                if(quantity == undefined)
                {
                    quantity = 1;
                }

                redSHOPB.shop.quantities.push(parseInt(quantity));
                element.find('.message').remove();
                element.find('.volumePrice').remove();

                redSHOPB.shop.productPrices[element.attr('data-product_id')] = element.html();
                element.html(element.html() + '<span class="message"><br/>' + redSHOPB.shop.loadingText + '&nbsp;' + redSHOPB.shop.image + '</span>')
            }
        );

        var settings = {
            url:redSHOPB.shop.url,
            type:'POST',
            dataType:'json',
            data: {
                product_ids: redSHOPB.shop.productIds,
                quantities: redSHOPB.shop.quantities,
                collection_ids: redSHOPB.shop.collectionIds,
                task: task,
                option: 'com_redshopb'
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                var alreadyInit = {};
                redSHOPB.shop.productPrice.each(function(index, element) {
                    element = jQuery(element);
                    element.find('.message').remove();

                    if (data[element.attr('data-product_id')]
                        && typeof data[element.attr('data-product_id')].displayPrice != "undefined"
                        && alreadyInit[element.attr('data-product_id')] === undefined) {
                        alreadyInit[element.attr('data-product_id')] = true;
                        var inp = element.closest('.productList-item').find('button.add-to-cart');

                        if (inp)
                        {
                            inp.attr('data-price', data[element.attr('data-product_id')].price);
                        }

                        var quantityInput = jQuery('input.quantityForOneProduct[data-product_id="'+ element.attr('data-product_id') +'"]');
                        quantityInput.attr('data-qty_min', parseInt(data[element.attr('data-product_id')].quantity_min));
                        quantityInput.attr('data-qty_max', parseInt(data[element.attr('data-product_id')].quantity_max));

                        element.html(data[element.attr('data-product_id')].displayPrice);
                    }
                });
                alreadyInit = {};
                jQuery(document).trigger('onProductAjaxPriceUpdate', data);
            });
    },

    updatePage:function(event, noPagination)
    {
        var targ = redSHOPB.form.getButtonTarget(event, true);
        var page = targ.attr('data-page');
        var pageTotal = targ.attr('data-page_total');

        var wrapper = jQuery('#pageProductList');
        var listCount = jQuery('.redshopb-productlist-count');
        var form = targ.closest('form');

        redSHOPB.form.getInput('page', form).val(page);
        redSHOPB.form.getInput('noPages', form).val(pageTotal);

        var settings = {
            url: form.attr('action'),
            type:'POST',
            dataType: 'json',
            data: redSHOPB.form.getData(form, 'shop.ajaxGetProductListPage'),
            beforeSend: function()
            {
                form.find('input:not(.dropCheckboxAccessory)').attr('disabled', 'disabled');
                wrapper.addClass('opacity-40');
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                if (noPagination === 1) {
                    // @todo refactor this method, so that we don't send the pagination value in the call.
                    // Instead put data-content-behavior = 'append'  attribute to wrapper on initial page load
                    wrapper.attr('data-content-behavior', 'append');
                }

                if ( data.urlPath != undefined && data.urlPath){
                    window.history.pushState({"html" :data.html, "productsCount": data.productsCount}, "", data.urlPath);
                }

				redSHOPB.shop.applyContent(data);
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                form.find('input:not(.dropCheckboxAccessory)').attr('disabled', false);
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');
                form.find('input:not(.dropCheckboxAccessory)').attr('disabled', false);

                if(wrapper.attr('data-content-behavior') != 'append')
                {
                    redSHOPB.shop.scrollTop(wrapper);
                }
            });
    },

    updateCategoriesPage:function(event)
    {
        var targ = redSHOPB.form.getButtonTarget(event, true);
        var page = targ.attr('data-page');
        var pageTotal = targ.attr('data-page_total');

        var wrapper = jQuery('#pageCategories');
        var form = targ.closest('form');

        redSHOPB.form.getInput('page', form).val(page);
        redSHOPB.form.getInput('noPages', form).val(pageTotal);

        var settings = {
            url: form.attr('action'),
            type:'POST',
            dataType: 'json',
            data: redSHOPB.form.getData(form, 'shop.ajaxGetCategoriesPage'),
            beforeSend: function()
            {
                wrapper.addClass('opacity-40');
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                redSHOPB.ajax.updateContent(wrapper, data.html);
                wrapper.find('select').chosen({"disable_search_threshold":10,"allow_single_deselect":true});

                if(data.collectionId)
                {
                    var tabUL = wrapper.find('#collectionTabs');
                    var tabs = wrapper.find('.tab-pane[id^="collection_"]');

                    tabs.each(function(index, element)
                    {
                        element = jQuery(element);

                        var classes = '';

                        if(element.hasClass('active'))
                        {
                            classes = 'active';
                        }

                        var tabMarkup = jQuery('<li class="' + classes +'"><a href="#' + element.attr('id') +'" data-toggle="tab">'+ element.attr('data-title')+'</a></li>');

                        tabUL.append(tabMarkup);
                    });
                }

                wrapper.trigger('change');
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');

                if(wrapper.attr('data-content-behavior') != 'append')
                {
	                redSHOPB.shop.scrollTop(jQuery('#pageCategories'));
                }
            });
    },

    scrollTop:function(wrapper)
    {
        jQuery('html, body').animate({scrollTop: wrapper.offset().top}, 500);
    },

    assignAddToFavoriteButtons:function()
    {
        if(typeof addToFavoriteList != 'function')
        {
            jQuery('.productList .add-to-favoritelist').remove();

            return;
        }

        jQuery('.productList').unbind('click').on('click', '.add-to-favoritelist', function (e)
        {
            e.preventDefault();
            e.stopPropagation();
            addToFavoriteList(this);
        });
    },

    addAllToCart:function(event)
    {
        var targ          = redSHOPB.form.getEventTarget(event);
        var productsShown = jQuery('#productsShown').val().split(',');
        var entries       = targ.attr('name').split('_');
        var collectionId  = entries[1];
        var quantities    = jQuery('input.quantityForOneProduct');
        var products      = [];

        if (quantities)
        {
            quantities.each(function() {
                var input    = jQuery(this);
                var quantity = parseFloat(input.val());
                var pid      = parseInt(input.attr('data-product_id'));

                if (quantity > 0)
                {
                    products.push({pid : pid, quantity: quantity});
                }
            });
        }
        else
        {
            productsShown.each(function(i, e) {
                products.push({pid : e, quantity: 1});
            });
        }

        var ajaxData = {
            collectionId : collectionId,
            products     : products
        };

        var cart    = jQuery('#redshopb-cart');
        var form    = cart.closest('form');
        var wrapper = cart.find('.redshopb-cart-content');

        var settings = {
            url        : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=cart.ajaxAddProductsToShoppingCart',
            data       : ajaxData,
            dataType   : 'json',
            cache      : false,
            type       : 'post',
            beforeSend : function (xhr)
            {
                wrapper.addClass('opacity-40');
                form.find('input').attr('disabled', true);
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                wrapper.html(data.body);
                redSHOPB.cart.updateCartButton(data);
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;

                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');
                form.find('input').attr('disabled', false);

                if (quantities)
                {
                    quantities.each(function()
                    {
                        jQuery(this).val(0);
                    });
                }
            });
    },

    addToCart:function(event)
    {
        //@todo refactor and simplify once VNR-4000 is completed
        var targ = redSHOPB.form.getEventTarget(event);
        var entries = targ.attr('name').split('_');
        var productId = entries[1];
        var collectionId = entries[2];
        var cartPrefix = '';
        var productQuantity = false;
        var attr_id = targ.attr("data-attribute-id");

        if(jQuery("input[name='" + attr_id + "']").val() == "") {
            targ.find(jQuery("input[name='" + attr_id + "']").val("1"));
        }

        if (3 in entries)
        {
            cartPrefix = '_'+entries[3];
        }

        var collectionPrefix = '';
        var isProduct = targ.hasClass('add-to-cart-product');

        // Collect all the information to send out

        var ajaxData = {
            'productId': productId
        };

        var accessorySelected = {};
        var accessoryQuantity = {};
        var checkboxAccessories = jQuery('.dropCheckboxAccessory_' + productId + cartPrefix);

        if (checkboxAccessories.length)
        {
            var nextIndex = 0;
            checkboxAccessories.each(function ()
            {
                var $this = jQuery(this);
                if ($this.is(':checked'))
                {
                    var checkboxAccessoryEntries = $this.attr('id').split('_');
                    checkboxAccessoryEntries[0] = 'quantityAccessory';
                    var quantityAccessory = jQuery('.'+checkboxAccessoryEntries.join('_'));

                    if (quantityAccessory.length)
                    {
                        if (jQuery.isNumeric(quantityAccessory.val()) && quantityAccessory.val() > 0)
                        {
                            accessorySelected[nextIndex] = $this.val();
                            accessoryQuantity[nextIndex] = quantityAccessory.val();
                            nextIndex = nextIndex +1;
                        }
                    }
                    else if (jQuery.isNumeric($this.val()) && $this.val() > 0)
                    {
                        accessorySelected[nextIndex] = $this.val();
                        nextIndex = nextIndex +1;
                    }
                }
            });
        }
        else
        {
            accessorySelected = jQuery('#dropDownAccessory_' + productId + cartPrefix).val();

            if (!accessorySelected || typeof accessorySelected === 'undefined' || accessorySelected.length == 0)
            {
                accessorySelected = {};
            }
        }

        if (Object.keys(accessorySelected).length)
        {
            ajaxData['accessorySelected'] = accessorySelected;
        }

        if (Object.keys(accessoryQuantity).length)
        {
            ajaxData['accessoryQuantity'] = accessoryQuantity;
        }

        var complimentarySelected = {};
        var complimentaryQuantity = {};
        var checkboxComplimentaryProducts = jQuery('.dropCheckboxComplimentary_' + productId + cartPrefix);

        if (checkboxComplimentaryProducts.length)
        {
            var nextIndex = 0;
            checkboxComplimentaryProducts.each(function ()
            {
                var $this = jQuery(this);
                if ($this.is(':checked')){
                    var checkboxComplimentaryEntries = $this.attr('id').split('_');
                    checkboxComplimentaryEntries[0] = 'quantityComplimentary';
                    var quantityComplimentary = jQuery('.'+checkboxComplimentaryEntries.join('_'));
                    if (quantityComplimentary.length){
                        if (jQuery.isNumeric(quantityComplimentary.val()) && quantityComplimentary.val() > 0)
                        {
                            complimentarySelected[nextIndex] = $this.val();
                            complimentaryQuantity[nextIndex] = quantityComplimentary.val();
                            nextIndex = nextIndex +1;
                        }
                    }
                    else if (jQuery.isNumeric($this.val()) && $this.val() > 0)
                    {
                        complimentarySelected[nextIndex] = $this.val();
                        nextIndex = nextIndex +1;
                    }
                }
            });
        }
        else
        {
            complimentarySelected = jQuery('#dropDownComplimentary_' + productId + cartPrefix).val();
            if (!complimentarySelected || typeof complimentarySelected === 'undefined' || complimentarySelected.length == 0)
            {
                complimentarySelected = {};
            }
        }

        if (Object.keys(complimentarySelected).length) {
            ajaxData['complimentarySelected'] = complimentarySelected;
        }

        if (Object.keys(complimentaryQuantity).length) {
            ajaxData['complimentaryQuantity'] = complimentaryQuantity;
        }

        if (collectionId != '' && collectionId != '0')
        {
            collectionPrefix = '#collection_' + collectionId + ' ';
            ajaxData['collectionId'] = collectionId;
        }

        if (isProduct)
        {
            ajaxData['currency'] = targ.data('currency');
            ajaxData['price'] = targ.data('price');
            productQuantity = jQuery('input[name=quantity_'+productId+'_'+collectionId+cartPrefix+']');

            if (productQuantity.length) {
                ajaxData['quantity'] = parseFloat(productQuantity.val());

                if (isNaN(ajaxData['quantity']) || ajaxData['quantity'] <= 0)
                {
                    redSHOPB.messages.displayMessage(Joomla.JText._('COM_REDSHOPB_NOTHING_SELECTED', 'Nothing Selected'), 'alert-warning', '');
                    return;
                }
            }

            var $stockroom = jQuery('.stockroom_'+productId + cartPrefix);
            if ($stockroom.length){
                if ($stockroom.is(':radio'))
                {
                    ajaxData['stockroom'] = $stockroom.filter(':checked').val();
                }
                else
                {
                    ajaxData['stockroom'] = $stockroom.val();
                }
            }

            redSHOPB.RSConfig._('CART_ITEM_KEYS').forEach(function(item, i)
            {
                if (ajaxData[item] == undefined)
                {
                    var $itemVariable = jQuery('.'+item+'_'+productId + cartPrefix);
                    if ($itemVariable.length)
                    {
                        if ($itemVariable.is(':radio'))
                        {
                            ajaxData[item] = $itemVariable.filter(':checked').val();
                        }
                        else
                        {
                            ajaxData[item] = $itemVariable.val();
                        }
                    }
                }
            });
        }
        else
        {
            var sendQuery = false;
            jQuery(collectionPrefix + 'input[name^=\"quantity_' + productId + '\"]').each(
                function (idx, ele)
                {
                    if (jQuery(ele).val() != '') {
                        var quantityEntries = jQuery(ele).attr('name').split('_');
                        var quantity = parseFloat(jQuery(ele).val());

                        if (!isNaN(quantity) && quantity > 0)
                        {
                            ajaxData['items[' + quantityEntries[2] + '][quantity]'] = quantity;
                            sendQuery = true;
                        }
                    }
                }
            );

            jQuery(collectionPrefix+ 'input[name^=\"currency_' + productId + '\"]').each(
                function (idx, ele)
                {
                    if (jQuery(ele).val() != '')
                    {
                        var currencyEntries = jQuery(ele).attr('name').split('_');
                        ajaxData['items[' + currencyEntries[2] + '][currency]'] = jQuery(ele).val();
                    }
                }
            );

            jQuery(collectionPrefix + 'input[name^=\"price_' + productId + '\"]').each(
                function (idx, ele)
                {
                    if (jQuery(ele).val() != '')
                    {
                        var priceEntries = jQuery(ele).attr('name').split('_');
                        ajaxData['items[' + priceEntries[2] + '][price]'] = jQuery(ele).val();
                    }
                }
            );

            jQuery(collectionPrefix + 'input[name^=\"dropDownSelected_' + productId + '\"]').each(
                function (idx, ele)
                {
                    if (jQuery(ele).val() != '')
                    {
                        var dropDownSelectedEntries = jQuery(ele).attr('name').split('_');
                        ajaxData['items[' + dropDownSelectedEntries[2] + '][dropDownSelected]'] = jQuery(ele).val();
                    }
                }
            );

            jQuery(collectionPrefix + 'select[name^=\"stockroom_' + productId + '\"]').each(
                function (idx, ele)
                {
                    if (jQuery(ele).val() != '')
                    {
                        var stockroomEntries = jQuery(ele).attr('name').split('_');
                        ajaxData['items[' + stockroomEntries[2] + '][stockroom]'] = jQuery(ele).val();
                    }
                }
            );
        }

        var cart = jQuery('#redshopb-cart');
        var form = cart.closest('form');
        var wrapper = cart.find('.redshopb-cart-content');
        var targContent = targ.html();

        var settings =
        {
            url       : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=cart.ajaxAddItemToShoppingCart',
            data      : ajaxData,
            dataType  : 'json',
            cache     : false,
            type      : 'post',
            beforeSend: function (xhr)
            {
                wrapper.addClass('opacity-40');
                form.find('input').attr('disabled', true);

                jQuery('input.amountInput' + cartPrefix).attr('disabled', true);
                targ
                    .html(redSHOPB.RSConfig._('CART_LOADING_IMAGE', '<img src="/media/com_redshopb/images/loading.gif" alt="" />'))
                    .addClass('disabled')
                    .attr('disabled', true);
            }
        };

        jQuery(redSHOPB.shop).trigger("onShopBeforeAddToCart", [settings]);

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                targ.find(jQuery("input[name='" + attr_id + "']").val(''));
                redSHOPB.messages.displayMessage(data.message, data.messageType, data.modal);

                if (data.messageType != 'alert-error')
                {
                    if (redSHOPB.RSConfig._('REDIRECT_AFTER_ADD_TO_CART') == '1') {
                        window.location.href = redSHOPB.RSConfig._('CART_PAGE');
                    }
                    else {
                        wrapper.html(data.body);
                        redSHOPB.cart.updateCartButton(data);
                    }
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;

                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                targ.html(targContent)
                    .removeClass('disabled')
                    .attr('disabled', null);

                wrapper.removeClass('opacity-40');
                form.find('input').attr('disabled', false);
                jQuery('input.amountInput' + cartPrefix).attr('disabled', false);
            });
    },

    updateCheckout: function() {
        var wrapper = jQuery('#shopcart');
        var form    = wrapper.find('form');
        var task    = 'shop.ajaxRefreshCheckout';

        form.find('input').attr('disabled', false);

        var settings = redSHOPB.ajax.getSettings(form, task);

        settings.beforeSend = function ()
        {
            wrapper.addClass('opacity-40');
            form.find('input').attr('disabled', true);
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                wrapper.html(data.body);

                var fooConfig = {
                    sort:false,
                    paginate:false,
                    breakpoints:
                    {
                        phone: 480,
                        tablet: 768,
                        default: rsbftDefault
                    }
                };

                wrapper.find('.ajax-quantity-change').on('change', function (event)
                {
                    redSHOPB.shop.cart.updateItemQuantity(event);
                });

                wrapper.find('.js-redshopb-footable').footable(fooConfig);
                var isDelayOrderParameters = jQuery('.isDelayOrderParameters');
                if (isDelayOrderParameters.length > 0){
					if (wrapper.find('#hasDelayProduct').val() == 1){
						isDelayOrderParameters.removeClass('hide');
					}else{
						isDelayOrderParameters.addClass('hide');
                    }
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');
                form.find('input').attr('disabled', false);
            });
    },

    getProductFormattedPrice:function(price, format, appendSymbol){

        price = redSHOPB.shop.number_format(
            price,
            format.decimals,
            format.decimal_separator,
            format.thousands_separator
        );

        if (appendSymbol === undefined)
        {
            appendSymbol = true;
        }

        var blankSpace = (format.blank_space == 1) ? '&nbsp;' : '';

        if (format.symbol_position == 0 && appendSymbol)
        {
            price = format.symbol + blankSpace + price;
        }
        else if (format.symbol_position == 1 && appendSymbol)
        {
            price = price + blankSpace + format.symbol;
        }

        return price;
    },

    number_format:function (number, decimals, dec_point, thousands_sep) {
        // Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    },

    updateProductsLimit: function (event)
    {
        var targ      = redSHOPB.form.getEventTarget(event);
        var form      = jQuery(targ.closest('form'));
        var taskInput = redSHOPB.form.getInput('task', form);
        taskInput.val('shop.updateProductsLimit');
        redSHOPB.form.getInput('noPages', form).val('0');
        form.submit();
    },

    loadProductVariant: function (input)
    {
        input = jQuery.parseJSON(input);

        input.task = 'shop.ajaxGetProductVariants';


        jQuery.ajax({
            url: 'index.php?option=com_redshopb',
            method: 'POST',
            data: input,
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                jQuery('#modalVariants-' + data.productId).html(data.body).modal('show');
            }
        });
    }
};

redSHOPB.shop.filters =
{
    filterProductList: function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');

        var filterWrapper = '#redshopb-filter-module-wrapper_' + form.attr('data-module_id');
        var productList = jQuery('#pageProductList');

        var settings = {
            url: form.attr('action'),
            type:'POST',
            dataType: 'json',
            data: redSHOPB.form.getData(form, 'shop.ajaxFilter'),
            beforeSend: function()
            {
                jQuery(filterWrapper).addClass('opacity-40');
                form.find('input').attr('disabled', 'disabled');
                productList.addClass('opacity-40');
                productList.closest('form').find('input').attr('disabled', 'disabled');
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
				if (data.urlPath != undefined && data.urlPath) {
					window.history.pushState({
						"html": data.html,
						"productsCount": data.productsCount,
						"moduleHtml": data.moduleHtml,
						"numberOfPages": data.numberOfPages,
						"filterWrapper": filterWrapper
					}, "", data.urlPath);
				}
                // Reset product list result
                productList.removeAttr("data-content-behavior");
				data.filterWrapper = filterWrapper;
				redSHOPB.shop.applyContent(data);
				form.find('input').attr('disabled', false);
				productList.closest('form').find('input').attr('disabled', false);
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                form.find('input').attr('disabled', false);
                productList.closest('form').find('input').attr('disabled', false);
            })
            .always(function(data, textStatus, jqXHR)
            {
                jQuery(filterWrapper).removeClass('opacity-40');
                productList.removeClass('opacity-40');
            });
    },

    loadFilters:function(event)
    {
        event.preventDefault();

	    var form = redSHOPB.form.getEventTarget(event);
	    var filterWrapper = jQuery('#redshopb-filter-module-wrapper_' + form.attr('data-module_id'));

	    var settings = {
		    url: form.attr('action'),
		    type:'POST',
		    dataType: 'json',
		    data: redSHOPB.form.getData(form, 'shop.ajaxGetFilters'),
		    beforeSend: function()
		    {
			    jQuery(filterWrapper).addClass('opacity-40');
			    form.find('input').attr('disabled', 'disabled');
		    }
	    };

	    jQuery.ajax(settings)
		    .done(function(data, textStatus, jqXHR)
		    {
			    filterWrapper.html(data.body).find('select').chosen({
				    "disable_search_threshold": 10,
				    "allow_single_deselect": true
			    });

			    form.find('input').attr('disabled', false);
		    })
		    .fail(function(jqXHR, textStatus, errorThrown)
		    {
			    data = jqXHR.responseJSON;
			    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
			    form.find('input').attr('disabled', false);
		    })
		    .always(function(data, textStatus, jqXHR)
		    {
			    filterWrapper.removeClass('opacity-40');
		    });
    },

    filterReset:function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');

        form.find('input[type="text"][name != "filter_search"][name != "filter_price"]').val('');
        form.find('input[type="hidden"][data-protected!="true"]').val('');
        form.find('input[type="select"]').val('');
        form.find('input[type="checkbox"]').prop('checked', false);
        form.find("input[type='checkbox'][value='']").prop("checked", true);
        form.find("input[type='radio'][value='']").prop("checked", true);

        var filterPrice = form.find('input[name="filter_price"]');

        if(filterPrice != 'undefined') {
            filterPrice.val('');
        }

        this.filterProductList(event);
    },

    showMore:function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);

        if(targ.prop('tagName') == 'SPAN')
        {
            targ = jQuery(targ.parent('a'));
        }

        var targetClass = targ.attr('data-target_class');

        if(redSHOPB.empty(targetClass))
        {
            return false;
        }

        jQuery(targetClass).has('input[type="checkbox"][checked != "checked"]').slideToggle();

        var icon = targ.find('i');
        icon.toggleClass('icon-plus-sign-alt').toggleClass('icon-minus-sign-alt');

        var textSpan = targ.find('span');

        if(icon.hasClass('icon-plus-sign-alt'))
        {
            textSpan.text(targ.attr('data-more_text'));
        }
        else
        {
            textSpan.text(targ.attr('data-less_text'));
        }
    }
};

redSHOPB.shop.checkout = {
    updateShippingDate: function (event)
    {
		var targ = redSHOPB.form.getEventTarget(event);
		var form = targ.closest('form');
		var fields = form.find('input');
		var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxCheckDeliveryDate');

		settings.beforeSend = function (xhr)
		{
			fields.attr('disabled', true);
		};

		jQuery.ajax(settings)
			.done(function(data)
			{
			    if (data.shippingDateResult != undefined){
					redSHOPB.shop.updateCheckout();
					jQuery.each(data.shippingDateResult, function(index, value) {
						if (value === true){
							jQuery('[name="'+index+'"]').removeClass('notProperDeliveryDateSelected');
                        }else{
							jQuery('[name="'+index+'"]').addClass('notProperDeliveryDateSelected');
                        }
					});
				}

				if (data.message !== undefined && data.message !== ''){
					redSHOPB.messages.displayMessage(data.message, data.messageType, '', true);
                }
			})
			.fail(function(jqXHR, textStatus, errorThrown)
			{
				var data = jqXHR.responseJSON;
				if (data.message !== undefined){
					redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                }
			})
			.always(function(data, textStatus, jqXHR)
			{
				fields.attr('disabled', false);
			});
    },

    updateDelivery: function (event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');

        var settings = redSHOPB.ajax.getSettings(form, 'shop.ajaxSetDeliveryAddress');
        var wrapper = jQuery('#delivery');

        settings.beforeSend = function (xhr)
        {
            wrapper.addClass('opacity-40');
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                wrapper.find('.js-address-wrapper').replaceWith(data.body);

                jQuery('#name').val(data.address.name);
                jQuery('#name2').val(data.address.name2);
                jQuery('#email').val(data.address.email);
                jQuery('#address').val(data.address.address);
                jQuery('#address2').val(data.address.address2);
                jQuery('#zip').val(data.address.zip);
                jQuery('#city').val(data.address.city);
                jQuery('#phone').val(data.address.phone);
                jQuery('#type').val(data.address.type);
                jQuery('#country_id').val(data.address.country_id).trigger('liszt:updated');
                redSHOPB.shop.checkout.checkCountry();

                if(redSHOPB.empty(data.address.state_name) === false)
                {
                    var $option = jQuery("<option selected></option>").val(data.address.state_id).text(data.address.state_name);
                    jQuery('#state_id').append($option).trigger('change');
                }

                jQuery('div#redshopb-delivery-info-address a#update-btn').css("display", "block");
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                wrapper.find('.js-address-wrapper').html('<p>' + data.message + '</p>');

                jQuery('div#redshopb-delivery-info-address a#update-btn').css("display", "none");
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');
            });
    },

    checkCountry: function ()
    {
        var selectedCountry = jQuery('#country_id').find('option:selected');

        var hasState = (redSHOPB.hasAttr(selectedCountry, 'data-has_state')
                        && redSHOPB.empty(selectedCountry.attr('data-has_state')) === false);
        if (hasState)
        {
            jQuery('.billingStateGroup').removeClass('hide');

            return;
        }

        jQuery('.billingStateGroup').addClass('hide');
    },

    saveAddress: function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var action = targ.attr('data-action');

        var form = targ.closest('form');
        var actionField = redSHOPB.form.getInput('action', form);
        var fields = form.find('input');

        actionField.val(action);
        var settings = redSHOPB.ajax.getSettings(form, 'shop.ajaxSaveDeliveryAddress');
        actionField.val('');

        var wrapper = jQuery('#delivery');

        settings.beforeSend = function ()
        {
            wrapper.addClass('opacity-40');
            fields.attr('disabled', true);
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                if(action == 'new')
                {
                    location.reload();

                    return;
                }

                wrapper.find('.js-address-wrapper').replaceWith(data.body);

                if(redSHOPB.empty(data.address.state_name) === false)
                {
                    var $option = jQuery("<option selected></option>").val(data.address.state_id).text(data.address.state_name);
                    jQuery('#state_id').append($option).trigger('change');
                }

                var deliveryAddressId =jQuery('#delivery_address_id');
                deliveryAddressId.trigger('change');
                redSHOPB.shop.checkout.checkCountry();
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');

            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');
                fields.attr('disabled', false);
            });
    },

	updateShippingMethods: function ()
	{
		var wrapper = jQuery('#shippingMethods');
        var form    = wrapper.closest('form');
        var task    = 'shop.ajaxRefreshShipping';

        var settings = redSHOPB.ajax.getSettings(form, task);

        settings.beforeSend = function ()
        {
            wrapper.addClass('opacity-40');
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                wrapper.html(data.body);
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                wrapper.removeClass('opacity-40');
            });
	}
};

redSHOPB.shop.cart = {
    updateItemQuantity: function (event)
    {
        var targ    = redSHOPB.form.getEventTarget(event);
        var cart    = redSHOPB.cart.getCartFromTarget(targ);
        var entries = targ.attr('name').replace('jform[', '').replace('][quantity]', '').split('_');
        var index   = targ.parents('tr').index() + 1;
        var tmp     = '';
        var data    = {
            quantity  : targ.val(),
            cartIndex : index
        };

        for (var i = 0; i < entries.length; i++)
        {
            tmp = entries[i].split('-');
            data[tmp[0]] = tmp[1];
        }

        var name = 'shop-cart-product-quantity' +
            '_' + data.customer + '_' + data.customerId +
            '_' + data.productId;

        if (data.productItem != undefined)
        {
            name += '_' + data.productItem;
        }
        else
        {
            name += '_0';
        }

        if (data.collectionId != undefined)
        {
            name += '_' + data.collectionId;
        }
        else
        {
            name += '_0';
        }

        if (data.keyAccessories != undefined)
        {
            name += '_' + data.keyAccessories;
        }
        else
        {
            name += '_';
        }

        if (data.stockroomId != undefined)
        {
            name += '_' + data.stockroomId;
        }
        else
        {
            name += '_0';
        }

        name +=  '_' + data.cartIndex;

        if (cart.length > 0)
        {
            var cartQ = jQuery(cart.find('input[name="' + name + '"]'));
            cartQ.val(data.quantity).trigger('change');
        }
        else
        {
            var form       = targ.closest('form');
            var cartItemHash = redSHOPB.form.getInput('cartItemHash', form).val(targ.data('cart_item_hash'));
            var quantity   = redSHOPB.form.getInput('quantity', form);
            var q          = 0.0;

            tmp = jQuery('input[name="' + targ.attr('name') + '"]');

            tmp.each(function() {
                q += parseFloat(jQuery(this).val());
            });

            quantity.val(q);

            var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxUpdateShoppingCartQuantity');

            cartItemHash.val('');
            quantity.val('');

            jQuery.ajax(settings)
                .done(function (data, textStatus, jqXHR) {
                    form.find('input').attr('disabled', false);
                    redSHOPB.shop.updateCheckout();
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    data = jqXHR.responseJSON;
                    form.find('input').attr('disabled', false);

                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                });
        }
    },

    removeItem: function (event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var cart = redSHOPB.cart.getCartFromTarget(targ);
        var name = targ.attr('name');

        if (cart.length > 0)
        {
            cart.find('button[name="' + name.replace('order-delete-item', 'shop-cart-product-remove') + '"]').trigger('click');
        }
        else
        {
            var form       = targ.closest('form');
            var cartItemHash = redSHOPB.form.getInput('cartItemHash', form);
            var quantity   = redSHOPB.form.getInput('quantity', form);

            cartItemHash.val(targ.data('cart_item_hash'));
            quantity.val(targ.parents('tr').find('input[name$="[quantity]"]').val());

            var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxRemoveItemFromShoppingCart');

            cartItemHash.val('');
            quantity.val('');

            jQuery.ajax(settings)
                .done(function (data, textStatus, jqXHR) {
                    form.find('input').attr('disabled', false);
                    redSHOPB.shop.updateCheckout();
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    data = jqXHR.responseJSON;
                    form.find('input').attr('disabled', false);

                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                });
        }
    },

	delayItem: function (event)
	{
		var targ = redSHOPB.form.getEventTarget(event);
		var form = targ.closest('form');
        var cartItemHash = redSHOPB.form.getInput('cartItemHash', form).val(targ.data('cart_item_hash'));
		var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxDelayItem');
        cartItemHash.val('');

		jQuery.ajax(settings)
			.done(function (data, textStatus, jqXHR) {
				form.find('input').attr('disabled', false);
				redSHOPB.messages.displayMessage(data.message, data.messageType, '');
				redSHOPB.shop.updateCheckout();
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				var data = jqXHR.responseJSON;
				form.find('input').attr('disabled', false);

				redSHOPB.messages.displayMessage(data.message, data.messageType, '');
			});
	},

	itemBackToMainOrder: function (event)
	{
		var targ = redSHOPB.form.getEventTarget(event);
		var form = targ.closest('form');
		var cartItemHash = redSHOPB.form.getInput('cartItemHash', form);
        cartItemHash.val(targ.data('cart_item_hash'));
		var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxItemBackToMainOrder');
        cartItemHash.val('');

		jQuery.ajax(settings)
			.done(function (data, textStatus, jqXHR) {
				form.find('input').attr('disabled', false);
				redSHOPB.messages.displayMessage(data.message, data.messageType, '');
				redSHOPB.shop.updateCheckout();
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				var data = jqXHR.responseJSON;
				form.find('input').attr('disabled', false);

				redSHOPB.messages.displayMessage(data.message, data.messageType, '');
			});
	},
};

redSHOPB.shop.productlist = {
    checkout: function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');
        redSHOPB.form.getInput('task', form).val('shop.checkout');
        jQuery(form).submit();
    },

    updateTotal: function()
    {
        var addToCartButtons = jQuery('#pageProductList').find('button.add-to-cart-product');
        var products         = [];
        var currency         = 0;
        var collection       = 0;

        addToCartButtons.each(function() {
            var btn       = jQuery(this);
            var qInput    = jQuery(btn.parent().find('input.quantityForOneProduct'));
            var nameData  = qInput.attr('name').split('_');
            var quantity  = qInput.val();
            var productId = btn.attr('data-product_id');
            var product   = {
                id       : productId,
                quantity : quantity
            };

            products.push(product);

            if (currency == 0)
            {
                currency = btn.attr('data-currency');
            }

            if (collection == 0)
            {
                collection = nameData[2];
            }
        });

        var total     = jQuery('#productsShopTotal');
        var totalWTax = jQuery('#productsShopTotalWithTaxes');

        if (total.length && totalWTax.length)
        {
            jQuery.ajax(
                {
                    url  : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=shop.ajaxGetProductsTotal',
                    data : {
                        products   : products,
                        currency   : currency,
                        collection : collection
                    },
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        total.html(redSHOPB.RSConfig._('CART_LOADING_IMAGE', '<img src="/media/com_redshopb/images/loading.gif" alt="" />'));
                        totalWTax.html(redSHOPB.RSConfig._('CART_LOADING_IMAGE', '<img src="/media/com_redshopb/images/loading.gif" alt="" />'));
                    }
                }
            ).done(function(data, textStatus, jqXHR)
            {
                if (data.total.length > 0)
                {
                    total.html(data.total);
                }

                if (data.totalWithTax.length > 0)
                {
                    totalWTax.html(data.totalWithTax);
                }
            }).fail(function(jqXHR, textStatus, errorThrown)
                {
                    data = jqXHR.responseJSON;
                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                }
            );
        }
    },

    updateCount: function ()
    {
        var productListQuantities = jQuery('input.quantityForOneProduct');
        var count = 0;

        productListQuantities.each(function() {
            var tmp = jQuery(this).val();

            if (tmp.length > 0)
            {
                count += parseInt(tmp);
            }
        });

        jQuery('#productsShopCount').html(count);
        redSHOPB.shop.productlist.updateTotal();
    }
};
