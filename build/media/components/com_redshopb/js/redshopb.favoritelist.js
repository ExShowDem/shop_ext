/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

redSHOPB.favoritelist =
{
    quantityTimer: null,
    lastEvent: null,

    productSelect: function (event)
    {
        if(event.keyCode != 13 && event.type != 'click')
        {
            return;
        }

        var targ             = jQuery(event.currentTarget);
        var form             = targ.closest('form');
        var productTitle     = targ.attr('data-text');
        var searchBox        = form.find('input[name="search"]');
        var product_id       = targ.attr('data-product_id');
        var pIdInput         = form.find('input[name="product_id"]');
        var attributeWrapper = jQuery("#myfavoritelists-attribute-container");
        var control          = jQuery("#redshopb-myfavoritelists-tool-addtocart-button");
        var productTitle     = targ.attr("data-text");
        control.on("click", redSHOPB.favoritelist.addProduct);
        searchBox.val(productTitle);
        pIdInput.val(product_id);

        var ajaxData = {
            productId : product_id
        };

        var settings = {
            url        : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=myfavoritelist.ajaxGetAttributeInputs',
            data       : ajaxData,
            dataType   : 'json',
            cache      : false,
            type       : 'post',
            beforeSend : function (xhr)
            {
                searchBox.addClass('loadingTextInput');
                form.find('input').attr('disabled', 'disabled');
                control.removeClass("btn-success").addClass("btn-muted disabled");
                attributeWrapper.empty();
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                jQuery('#js-product-search-results').html('').parent('.row-fluid').addClass('hidden');
                control.removeClass("btn-muted disabled").addClass("btn-success");
                attributeWrapper.html(data.body);
                redSHOPB.favoritelist.populateProductItem();
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                searchBox.val(productTitle).removeClass('loadingTextInput');
                form.find('input').attr('disabled', false);
            });
    },

    addToCart: function(event)
    {
        var targ          = jQuery(event.currentTarget);
        var form          = targ.closest('form');
        var cart          = jQuery('#redshopb-cart');
        var wrapper       = cart.find('.redshopb-cart-content');
        var cartPrefix    = '';
        var targContent   = targ.html();
        var productId     = targ.attr("data-product_id");
        var productItemId = targ.attr("data-attribute-id");
        var quantity      = targ.closest('tr').find('.quantityValue').val();
        var price         = targ.attr("data-price");
        var currency      = targ.attr("data-currency");
        var ajaxData      = {
            'productId': productId
        };

        if (productItemId == 0)
        {
            ajaxData['quantity'] = quantity;
            ajaxData['price']    = price;
            ajaxData['currency'] = currency;
        }
        else
        {
            ajaxData['items[' + productItemId + '][quantity]'] = quantity;
            ajaxData['items[' + productItemId + '][price]']    = price;
            ajaxData['items[' + productItemId + '][currency]'] = currency;
            ajaxData['items[' + productItemId + '][dropDownSelected]'] = '1';
        }

        var settings = {
            url: redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=cart.ajaxAddItemToShoppingCart',
            data: ajaxData,
            dataType: 'json',
            cache: false,
            type: 'post',
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

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                redSHOPB.messages.displayMessage(data.message, data.messageType, data.modal);

                if (data.messageType != 'alert-error')
                {
                    if (redSHOPB.RSConfig._('REDIRECT_AFTER_ADD_TO_CART') == '1')
                    {
                        window.location.href = redSHOPB.RSConfig._('CART_PAGE');
                    }
                    else
                    {
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

    addProduct: function(event)
    {
        var control         = jQuery("#redshopb-myfavoritelists-tool-addtocart-button");
        var targ            = event.target;
        var form            = targ.closest('form');
        var token           = jQuery('#token input');
        var task            = 'myfavoritelist.ajaxAddProduct';
        var search          = jQuery(form).find("input[name=search]").val();
        var result_layout   = jQuery(form).find("input[name=result_layout]").val();
        var product_id      = jQuery(form).find("input[name=product_id]").val();
        var collection_id   = jQuery(form).find("input[name=collection_id]").val();
        var fav_id          = jQuery(form).find("input[name=fav_id]").val();
        var simple_search   = jQuery(form).find("input[name=simple_search]").val();
        var product_item_id = jQuery(form).find("input[id=myfavoritelist-item-id]").val();

        if (!product_item_id)
        {
            product_item_id = 0;
        }

        var data = {
            search          : search,
            task            : task,
            result_layout   : result_layout,
            product_id      : product_id,
            collection_id   : collection_id,
            fav_id          : fav_id,
            simple_search   : simple_search,
            product_item_id : product_item_id
        };

        data[token.attr("name")] = token.val();

        var settings = {
            url: jQuery(form).attr('action'),
            type:'POST',
            dataType: 'json',
            data: data,
            beforeSend: function()
            {
                jQuery('#js-product-list-wrapper').addClass('opacity-40');
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR, form)
            {
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                jQuery('#js-product-list-wrapper').replaceWith(data.html);
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                jQuery('#js-product-list-wrapper').removeClass('opacity-40');
                control.removeClass("btn-success").addClass("btn-muted disabled");
                control.off(event);
                jQuery("#js-product-search").val("");
                jQuery("#js-product-search").focus();
                jQuery("#myfavoritelists-attribute-container").empty();
            });
    },

    populateProductItem: function(event)
    {
        var attributeValues = [];

        jQuery(".myfavoritelist-product-attribute").each(function() {
            attributeValues.push(jQuery(this).val());
        });

        var ajaxData = {
            attributeValues : attributeValues
        };

        var settings = {
            url        : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=myfavoritelist.ajaxGetProductItem',
            data       : ajaxData,
            dataType   : 'json',
            cache      : false,
            type       : 'post'
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                jQuery("#myfavoritelist-item-id").val(data.productItemId);
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            });
    },

    removeProduct: function(event)
    {
        var targ = redSHOPB.form.getButtonTarget(event);

        var form = targ.closest('form');
        var productIdInput = form.find('input[name="product_id"]');
        var productItemIdInput = form.find('input[name="product_item_id"]');
        productIdInput.val(targ.attr('data-product_id'));
        productItemIdInput.val(targ.attr('data-product_item_id'));
        data = redSHOPB.form.getData(form, 'myfavoritelist.ajaxRemoveProductInList');
        productIdInput.val('');
        productItemIdInput.val('');

        var settings = {
            url: form.attr('action'),
            type:'POST',
            dataType: 'json',
            data: data,
            beforeSend: function()
            {
                jQuery('#js-product-list-wrapper').addClass('opacity-40');
                form.find('input').attr('disabled', 'disabled');
            }
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                jQuery('#js-product-list-wrapper').replaceWith(data.html);
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                var data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                jQuery('#js-product-list-wrapper').removeClass('opacity-40');
                form.find('input').attr('disabled', false);
            });
    },


    updatePrice:function(event)
    {
        var targetInput = redSHOPB.form.getEventTarget(event);
        var form = targetInput.closest('form');
        jQuery('#js-product-list-wrapper').addClass('opacity-40');
        form.find('input').attr('readonly', 'readonly');
        form.find('button').attr('disabled', 'disabled');

        clearTimeout(this.quantityTimer);
        this.lastEvent = event;
        this.quantityTimer = setTimeout(function(){redSHOPB.favoritelist.doPriceUpdate(redSHOPB.favoritelist.lastEvent)}, 500);
    },

    doPriceUpdate:function(event)
    {
        var targetInput = jQuery(event.target);
        if (event.currentTarget != null)
            targetInput = redSHOPB.form.getEventTarget(event);

        var product_id = targetInput.attr('data-product_id');
        var form = targetInput.closest('form');
        var data = redSHOPB.form.getData(form, 'myfavoritelist.ajaxUpdateProductTable');

        var settings = {
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json',
            data: data
        };

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                jQuery('#js-product-list-wrapper').replaceWith(data.html);
				jQuery('#js-product-list-wrapper').find('select').chosen({"disable_search_threshold":10,"allow_single_deselect":true});
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                var data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function(data, textStatus, jqXHR)
            {
                jQuery('#js-product-list-wrapper').removeClass('opacity-40');
                form.find('input').attr('disabled', false);
                form.find('input[name="quantity['+ product_id +']"]').focus();
            });
    },

    requestOffer:function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');

        form.find('input[name="task"]').val('myfavoritelist.requestOffer');
        form.submit();
    }
};
