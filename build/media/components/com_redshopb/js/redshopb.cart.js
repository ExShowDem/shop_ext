/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

redSHOPB.cart = {

    initSavedCart: function () {
        jQuery(".btn-remove-saved-cart")
            .attr('href', 'javascript:void(0);')
            .click(function (event) {
                redSHOPB.cart.deleteSavedCart(event);
            });

        // Checkout cart process
        var checkoutButton = jQuery(".btn-checkout-saved-cart");
        checkoutButton.attr('onclick', '');
        checkoutButton.unbind('click');

        checkoutButton.click(function (event) {
            redSHOPB.cart.checkOutSavedCart(event)
        });

        jQuery(".btnRemoveCartItem").unbind('click').click(function (event) {

            redSHOPB.cart.removeSavedCartItem(event);
        });
    },

    deleteSavedCart: function (event) {
        var target = redSHOPB.form.getEventTarget(event);
        var form = target.closest('form');
        var cartId = target.attr("data-id");

        var cartIdInput = redSHOPB.form.getInput('cartId', form);
        cartIdInput.val(cartId);

        var loadingImageClass = '.img-loading-cart-' + cartId;

        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxDeleteSavedCart()');
        settings.beforeSend = function () {
            jQuery(loadingImageClass).css('visibility', 'visible');
        };

        jQuery.ajax(settings)
            .done(function (data, textStatus, jqXHR) {
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                jQuery('#savedCartsTable tr#row-' + cartId).hide('slow', function () {
                    jQuery('#savedCartsTable tr#row-' + cartId).remove();
                });
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                jQuery(loadingImageClass).css('visibility', 'hidden');
            });
    },

    checkOutSavedCart: function (event) {
        var target = redSHOPB.form.getEventTarget(event);
        var form = target.closest('form');

        if (redSHOPB.hasAttr(target, 'data-form')) {
            form = jQuery('#' + target.attr('data-form'));
        }

        var cartId = target.attr("data-id");
        var cartIdInput = redSHOPB.form.getInput('cartId', form);
        cartIdInput.val(cartId);

        var task = redSHOPB.form.getInput('task', form);
        task.val('cart.checkoutCart');
        form.submit();
    },

    removeSavedCartItem: function (event) {
        var target = redSHOPB.form.getEventTarget(event);
        var form = target.closest('form');
        var cartItem = target.attr("data-id");

        var cartItemInput = redSHOPB.form.getInput('cartItem', form);
        cartItemInput.val(cartItem);

        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxRemoveSavedCartItem');

        jQuery.ajax(settings)
            .done(function (data, textStatus, jqXHR) {
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                target.closest('tr').hide('slow', function () {
                    jQuery(this).remove();
                })
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');

            });
    },

    toggleCart: function (event) {
        var targ = redSHOPB.form.getEventTarget(event);

        if (redSHOPB.hasAttr(targ, 'data-force-close')) {
            return redSHOPB.cart.closeCart(event);
        }

        if (redSHOPB.hasAttr(targ, 'data-force-show')) {
            return redSHOPB.cart.showCart(event);
        }

        var cart = redSHOPB.cart.getCartFromTarget(targ);

        cart.toggle();

        if (redSHOPB.hasAttr(targ, 'data-close-after')) {
            setTimeout(function () {
                cart.hide(2000);
            }, 3000);
        }
    },

    showCart: function (event) {
        var targ = redSHOPB.form.getEventTarget(event);
        var cart = redSHOPB.cart.getCartFromTarget(targ);
        cart.show();

        if (redSHOPB.hasAttr(targ, 'data-close-after')) {
            setTimeout(function () {
                cart.hide(2000);
            }, 3000);
        }

        return true;
    },

    getCartFromTarget: function (targ) {
        var cartId = '#redshopb-cart';

        // Default behavior
        if (redSHOPB.hasAttr(targ, 'data-cart-id')) {
            cartId = '#' + targ.attr('data-cart-id');

        }

        return jQuery(cartId);
    },

    closeCart: function (event) {
        var targ = redSHOPB.form.getEventTarget(event);
        var cart = redSHOPB.cart.getCartFromTarget(targ);

        return cart.hide();
    },

    getShoppingCart: function (formId) {
        var form = jQuery('#' + formId);
        var cart = redSHOPB.cart.getCartFromTarget(form);
        var wrapper = cart.find('.redshopb-cart-content');
        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxGetShoppingCart');

        redSHOPB.cart.executeCartAjax(cart, form, wrapper, settings);
    },

    executeCartAjax: function (targ, form, wrapper, settings, callback) {
        settings.beforeSend = function () {
            wrapper.addClass('opacity-40');
            form.find('input').attr('disabled', true);
        };

        jQuery.ajax(settings)
            .done(function (data, textStatus, jqXHR) {
                wrapper.html(data.body);
                form.find('input').attr('disabled', false);
                redSHOPB.cart.updateCartButton(data);

                if (data.message.length > 0 && data.messageType.length > 0)
                {
                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                }

                if (redSHOPB.hasAttr(targ,'data-checkout')) {
                    redSHOPB.shop.updateCheckout();
                }

                if (callback !== null && typeof callback === 'function')
                {
                    callback();
                }
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                data = jqXHR.responseJSON;
                form.find('input').attr('disabled', false);

                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            })
            .always(function (data, textStatus, jqXHR) {
                wrapper.removeClass('opacity-40');
            });
    },

    updateCartButton: function (data) {
        var shopLink = jQuery('#redshopb-cart-link');

        shopLink.find('.redshopb-cart-items').html(data.quantity);

        jQuery.each(data.formatted_totals, function (index, value) {
            var currencyTotalPriceContainer = shopLink.find('.total-value .oneCurrencyTotal[data-currency="' + index + '"]');
            currencyTotalPriceContainer.html(value);
        });
    },

    removeItemFromShoppingCart: function (event, callback) {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');
        var cart = redSHOPB.cart.getCartFromTarget(form);

        var cartItemHash = redSHOPB.form.getInput('cartItemHash', form);
        var quantity   = redSHOPB.form.getInput('quantity', form);

        cartItemHash.val(targ.data('cart_item_hash'));
        var q = jQuery('input[name="' + targ.attr('name').replace('-remove_', '-quantity_') + '"]').val();
        quantity.val(q);
        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxRemoveItemFromShoppingCart');
        cartItemHash.val('');
        quantity.val('');

        var wrapper = cart.find('.redshopb-cart-content');
        redSHOPB.cart.executeCartAjax(targ, form, wrapper, settings, callback);
    },

    updateShoppingCartItemQuantity: function (event) {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');
        var cart = redSHOPB.cart.getCartFromTarget(form);

        var cartItemHash = redSHOPB.form.getInput('cartItemHash', form);
        var quantity = redSHOPB.form.getInput('quantity', form);
        var q = 0;

		cartItemHash.val(targ.data('cart_item_hash'));

        jQuery(form).find('input[data-cart_item_hash="' + cartItemHash.val() + '"]').each(function() {
            q += parseFloat(jQuery(this).val());
        });

        quantity.val(q);

        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxUpdateShoppingCartQuantity');

        cartItemHash.val('');
        quantity.val('');

        var wrapper = cart.find('.redshopb-cart-content');
        redSHOPB.cart.executeCartAjax(targ, form, wrapper, settings);
    },

    removeOfferFromShoppingCart: function (event) {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');
        var cart = redSHOPB.cart.getCartFromTarget(form);
        var offerCode = redSHOPB.form.getInput('offerCode', form);

        offerCode.val(targ.attr('name'));
        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxRemoveOfferFromShoppingCart');
        offerCode.val('');

        var wrapper = cart.find('.redshopb-cart-content');

        redSHOPB.cart.executeCartAjax(targ, form, wrapper, settings);
    },

    clearShoppingCart: function (event) {
        var targ = redSHOPB.form.getEventTarget(event);
        var form = targ.closest('form');
        var cart = redSHOPB.cart.getCartFromTarget(form);
        var settings = redSHOPB.ajax.getSettings(form, 'cart.ajaxClearShoppingCart');

        var wrapper = cart.find('.redshopb-cart-content');
        redSHOPB.cart.executeCartAjax(targ, form, wrapper, settings);
    }
};
