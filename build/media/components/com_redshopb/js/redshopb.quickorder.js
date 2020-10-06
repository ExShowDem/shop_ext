/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

redSHOPB.quickorder =
    {
        successMessage: null,
        errorMessage: null,
        defaultUnit: null,

        init: function (successMessage, errorMessage) {
            redSHOPB.quickorder.successMessage = successMessage;
            redSHOPB.quickorder.errorMessage = errorMessage;

            jQuery("#redshopb-quickorder-table button.shopping-cart-remove").click(function (event) {
                jQuery("#redshopb-quickorder-table tr#" + jQuery(this).data("tr")).hide('normal', function () {
                    jQuery(this).remove();
                });

                redSHOPB.quickorder.removeItem(event);
            });
        },

        restForm: function (form, event) {
            form.find("input[name=\"product_id\"]").val("");
            var quantity = form.find("input[name=\"quantity\"]");
            quantity.val("");
            quantity.unbind("keydown");

            if (redSHOPB.quickorder.defaultUnit != null) {
                jQuery("#redshopb-quickorder-tool-unit-name").text(redSHOPB.quickorder.defaultUnit);
            }

            var control = jQuery("#redshopb-quickorder-tool-addtocart-button");
            control.off(event);
            control.addClass("btn-muted disabled").removeClass("btn-success");

            form.find("input[name=\"collection_id\"]").val("");
            form.find("input[name=\"currency\"]").val("");
            form.find("input").attr("disabled", false);

        },

        productSelect: function (event) {
            var targ = jQuery(this);

            var form = targ.closest("form");
            redSHOPB.quickorder.restForm(form);

            var unit = targ.attr("data-unit_measure");
            var product_id = targ.attr("data-product_id");
            var currency = targ.attr("data-currency");
            var collectionId = targ.attr("data-collection_id");
            var productTitle = targ.attr("data-text");
            var packageSize = targ.attr("data-pkg_size");

            if (isNaN(packageSize) || packageSize <= 0) {
                packageSize = 1;
            }

            if (typeof unit != "undefined") {
                var unitSpan = jQuery("#redshopb-quickorder-tool-unit-name");

                if (redSHOPB.quickorder.defaultUnit == null) {
                    redSHOPB.quickorder.defaultUnit = unitSpan.text();
                }

                unitSpan.text(unit);
            }

            var input = jQuery("#js-product-search");
            input.val(productTitle);
            form.find("input[name=\"product_id\"]").val(product_id);
            form.find("input[name=\"collection_id\"]").val(collectionId);
            form.find("input[name=\"currency\"]").val(currency);

            var attributeWrapper = jQuery("#quickorder-attribute-container");
            var ajaxData = {
                productId : product_id
            };

            var settings = {
                url        : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=quick_order.ajaxGetAttributeInputs',
                data       : ajaxData,
                dataType   : 'json',
                cache      : false,
                type       : 'post',
                beforeSend : function (xhr)
                {
                    attributeWrapper.empty();
                }
            };

            jQuery.ajax(settings)
                .done(function(data, textStatus, jqXHR)
                {
                    attributeWrapper.html(data.body);
                    redSHOPB.quickorder.populateProductItem();
                })
                .fail(function(jqXHR, textStatus, errorThrown)
                {
                    data = jqXHR.responseJSON;
                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                });

            var quantity = form.find("input[name=\"quantity\"]");
            quantity.val(packageSize);
            quantity.attr("min", packageSize);
            quantity.attr("step", packageSize);
            quantity.on("keydown", redSHOPB.quickorder.addToCart);

            var control = jQuery("#redshopb-quickorder-tool-addtocart-button");
            control.on("click", redSHOPB.quickorder.addToCart);
            control.removeClass("btn-muted disabled").addClass("btn-success");

            form.find("input[name=\"quantity\"]").focus();
            jQuery("#js-product-search-results").html("").parent(".row-fluid").addClass("hidden");
        },

        populateProductItem: function (event) {
            var attributeValues = [];

            jQuery(".quickorder-product-attribute").each(function() {
                attributeValues.push(jQuery(this).val());
            });

            var currency     = jQuery("input[name=\"currency\"]").val();
            var collectionId = jQuery("input[name=\"collection_id\"]").val();

            var ajaxData = {
                attributeValues : attributeValues,
                currencyId      : currency,
                collectionId    : collectionId
            };

            var settings = {
                url        : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=quick_order.ajaxGetProductItem',
                data       : ajaxData,
                dataType   : 'json',
                cache      : false,
                type       : 'post'
            };

            jQuery.ajax(settings)
                .done(function(data, textStatus, jqXHR)
                {
                    jQuery("#quickorder-item-id").val(data.productItemId);
                    jQuery("#quickorder-item-price").val(data.price);

                    if (jQuery("#quick_order_product_custom_text").length !== 0)
                    {
                        redSHOPB.quickorder.renderCustomText();
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown)
                {
                    data = jqXHR.responseJSON;
                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                });
        },

        renderCustomText: function (event) {
            var productId = jQuery(".redshopb-quickorder-tool form").find("input[name=\"product_id\"]").val();
            var customTextWrapper = jQuery("#quick_order_product_custom_text");

            var ajaxData = {
                productId : productId
            };

            var settings = {
                url        : redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=quick_order.ajaxRenderCustomText',
                data       : ajaxData,
                dataType   : 'json',
                cache      : false,
                type       : 'post'
            };

            jQuery.ajax(settings)
                .done(function(data, textStatus, jqXHR)
                {
                    customTextWrapper.html(data.body);
                })
                .fail(function(jqXHR, textStatus, errorThrown)
                {
                    data = jqXHR.responseJSON;
                    redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                });
        },

        addToCart: function (event) {
            if (event.keyCode !== 13 && event.type !== "click") {
                return;
            }

            var targ = redSHOPB.form.getButtonTarget(event);
            var form = targ.closest("form");
            var wrapper = jQuery("#redshopb-cart").find(".redshopb-cart-content");

            var productId = form.find("input[name=\"product_id\"]").val();

            if (form.find("input[name=\"search\"]").val().length >= 3
                && productId.length === 0) {
                var amount = jQuery('#redshopb-quickorder-tool-quantity').val();
                jQuery('#js-product-search-results > div > div > a:nth-child(2)').trigger('click');
                jQuery('#redshopb-quickorder-tool-quantity').val(amount);
                jQuery('#redshopb-quickorder-tool-addtocart-button').trigger('click');
                return true;
            }

            if (productId.length == 0) {
                redSHOPB.messages.displayMessage(redSHOPB.quickorder.errorMessage, "alert-danger", "");
                return false;
            }

            var quantity     = jQuery('#redshopb-quickorder-tool-quantity').val();
            var itemId       = jQuery("#quickorder-item-id").val();
            var itemPrice    = jQuery("#quickorder-item-price").val();
            var itemCurrency = jQuery("input[name=\"currency\"]").val();

            var ajaxData = {
                'productId': productId,
                'quantity' : quantity
            };

            if (itemId.length > 0)
            {
                ajaxData['items[' + itemId + '][quantity]'] = quantity;
                ajaxData['items[' + itemId + '][price]']    = itemPrice;
                ajaxData['items[' + itemId + '][currency]'] = itemCurrency;
            }

            var settings = {
                url: redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=cart.ajaxAddItemToShoppingCart',
                type: "POST",
                dataType: "json",
                data: ajaxData,
                beforeSend: function () {
                    jQuery("#js-product-search").addClass("loadingTextInput");
                    form.find("input").attr("disabled", "disabled");
                }
            };

            jQuery(redSHOPB.shop).trigger("onShopBeforeAddToCart", [settings]);

            jQuery.ajax(settings).done(function (data) {
                redSHOPB.messages.displayMessage(redSHOPB.quickorder.successMessage, "alert-success", "");
                redSHOPB.quickorder.restForm(form, event);
                jQuery("#js-product-search").val("");
                jQuery("#quickorder-attribute-container").empty();
                jQuery("#js-product-search").focus();
                wrapper.html(data.body);
                redSHOPB.cart.updateCartButton(data);
                redSHOPB.quickorder.orderTableRefresh(data.items);
                redSHOPB.quickorder.updateTotal(data.formatted_taxes, data.formatted_totals);
                jQuery("#quick_order_product_custom_text").empty();
            }).fail(function (jqXHR) {
                data = jqXHR.responseJSON;
                redSHOPB.messages.displayMessage(redSHOPB.quickorder.errorMessage, "alert-danger", "");
            }).always(function () {
                if (typeof getCart === "function") {
                    getCart(true);
                }

                jQuery("#js-product-search").removeClass("loadingTextInput");
                form.find("input").attr("disabled", false);
            });
        },

        orderTableRefresh: function (products) {
            var formToken = jQuery("#redshopb-quickorder-tool-token").attr("name");
            var tableBody = jQuery("#redshopb-quickorder-table tbody");
            tableBody.html("");

            for (var i = 0; i < products.length; i++) {
                var product = products[i];

                // Add new row
                var row = jQuery("<tr>");

                row.attr("id", product.productId);

                // Add product name
                var productNameTd = "<span class='quickorder-product-name'>" + product.name + "</span><br /><span class='quickorder-product-sku'>" + product.sku + "</span>";

                if (typeof product.customTextLabel !== "undefined" && typeof product.customText !== "undefined")
                {
                    productNameTd += "<div><span class=\"custom_text_label\">"+product.customTextLabel+":</span><span class=\"custom_text_value\">"+product.customText+"</span></div>";
                }

                jQuery("<td>").html(productNameTd).appendTo(jQuery(row));

                // Add quantity
                jQuery("<td>").html("<span class='quickorder-product-quantity'>" + product.quantity + "</span>").appendTo(jQuery(row));

                // Add product discount
                jQuery("<td>").html("<span id='discount_price_" + product.productId + "'></span>").appendTo(jQuery(row));

                // Add product total price
                jQuery("<td>").html("<span data-currency='" + product.currency + "' class='total-price' data-real='" + price + "' id='total_price_" + product.productId + "'></span>").appendTo(jQuery(row));

                // Add delete button
                jQuery("<td>").append(
                    jQuery("<button>").addClass("btn btn-mini shopping-cart-remove")
                        .attr("type", "button")
                        .attr("data-tr", product.productId)
                        .attr("name", "shop-cart-product-remove___" + product.productId + "_0_0__")
                        .attr("data-cart_item_hash", product.hash)
                        .click(function (e) {
                            jQuery("#redshopb-quickorder-table tr#" + jQuery(this).data("tr")).hide("normal", function () {
                                jQuery(this).remove();
                            });

                            redSHOPB.quickorder.removeItem(e);
                        }).html("<i class='icon-trash'></i>")
                ).appendTo(jQuery(row));

                // Update price values
                var discountPrice = product.quantity * product.price * (product.discount / 100);
                discountPrice = (isNaN(discountPrice)) ? 0 : discountPrice;
                redSHOPB.quickorder.formatPrice(
                    discountPrice,
                    product.currency,
                    "#redshopb-quickorder-table #discount_price_" + product.productId,
                    formToken
                );

                var price = product.quantity * product.price;
                price = (isNaN(price)) ? 0 : price;
                redSHOPB.quickorder.formatPrice(
                    price,
                    product.currency,
                    "#redshopb-quickorder-table #total_price_" + product.productId,
                    formToken
                );

                // Add row to table
                jQuery(row).hide().appendTo(tableBody).show("slow");
            }
        },

        updateTotal: function (taxes, totals) {
            var vatDiv = jQuery("#redshopb-quickorder-vat");
            var totalDiv = jQuery("#redshopb-quickorder-totalprice");
            vatDiv.html("");
            totalDiv.html("");

            // Calculate taxes
            jQuery.each(taxes, function (currency, tax) {
                var taxDiv = jQuery("<div>").attr("id", "vat_" + currency).attr("class", "pull-right").html(tax);
                taxDiv.appendTo(vatDiv);
            });

            // Calculate totals
            jQuery.each(totals, function (currency, total) {
                var tDiv = jQuery("<div>").attr("id", "total_price_" + currency).attr("class", "pull-right").html(total);
                tDiv.appendTo(totalDiv);
            });
        },

        formatPrice: function (price, currency, selector, formToken) {
            var data = {
                "currency": currency,
                "price": price
            };

            data[formToken] = 1;

            jQuery.ajax({
                url: redSHOPB.RSConfig._("SITE_URL") + "index.php?option=com_redshopb&task=quick_order.ajaxGetFormattedPrice",
                data: data,
                type: "POST"
            }).done(function (response) {
                jQuery(selector).attr("data-real", price).html(response);
            });
        },

        formatQuantity: function (quantity, productId, selector, formToken) {
            var data = {
                "quantity": quantity,
                "productId": productId
            };

            data[formToken] = 1;

            jQuery.ajax({
                url: redSHOPB.RSConfig._("SITE_URL") + "index.php?option=com_redshopb&task=quick_order.ajaxGetQuantityWithUnitMeasure",
                data: data,
                type: "POST"
            }).done(function (response) {
                jQuery(selector).html(response);
            });
        },

        removeItem: function (event) {
            redSHOPB.cart.removeItemFromShoppingCart(event, redSHOPB.quickorder.refreshCart);
        },

        refreshCart: function () {
            var data = {};

            data[redSHOPB.RSConfig._("TOKEN")] = 1;

            var settings = {
                url: "index.php?option=com_redshopb&task=cart.ajaxGetShoppingCart",
                type: "POST",
                dataType: "json",
                data: data,
                beforeSend: function () {
                    jQuery("#js-product-search").addClass("loadingTextInput");
                }
            };

            jQuery.ajax(settings).done(function (data) {
                redSHOPB.quickorder.orderTableRefresh(data.items);
                redSHOPB.quickorder.updateTotal(data.formatted_taxes, data.formatted_totals);
            }).always(function () {
                if (typeof getCart === "function") {
                    getCart(true);
                }

                jQuery("#js-product-search").removeClass("loadingTextInput");
            });
        }
    };
