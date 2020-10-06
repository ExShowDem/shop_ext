var cartLoaded = false

function restoreCart (cartId) {
    var jsonData = {
        'cartId': cartId,
    }

    // variable value as json key
    jsonData[redSHOPB.RSConfig._('token')] = 1
    jQuery.ajax({
        url: redSHOPB.RSConfig._('SITE_URL') +
        'index.php?option=com_redshopb&task=cart.ajaxCheckoutCart',
        data: jsonData,
        type: 'post',
    }).done(function (data) {
        if (data == '1') {
            getCart(true)
        }
    })
}

function removeAccessories (accessories) {

    var ajaxData = {
        'accessories': accessories,
    }
    jQuery.ajax({
        url: redSHOPB.RSConfig._('SITE_URL') +
        'index.php?option=com_redshopb&task=shop.ajaxRemoveAccessories',
        data: ajaxData,
        cache: false,
        type: 'POST',
        beforeSend: function (xhr) {
            jQuery('#cart-spinner').show()
            jQuery('.redshopb-cart-content').addClass('opacity-40')
            jQuery('#cart-productList').remove()
        },
    }).done(function (data) {

    })
}

function assignAddToFavoriteButtons () {
    jQuery('.productList').
        unbind('click').
        on('click', '.add-to-favoritelist', function (e) {
            e.preventDefault()
            e.stopPropagation()
            addToFavoriteList(this)
        })
}

function addToFavoriteList (buttonElement) {
    if (jQuery(buttonElement).data('favoriteopen') == '1') {
        jQuery(buttonElement).data('favoriteopen', '0').popover('destroy')
        return
    }

    var entries = jQuery(buttonElement).attr('name').split('_')
    var productId = entries[1]
    var collectionId = entries[2]

    var ajaxDataOrig = {
        'product_id': productId,
    }

    jQuery.ajax({
        url: redSHOPB.RSConfig._('SITE_URL') +
        'index.php?option=com_redshopb&task=shop.ajaxGetFavorites',
        data: ajaxDataOrig,
        cache: false,
        type: 'post',
        beforeSend: function (xhr) {
        },
    }).done(function (data) {
        jQuery(buttonElement).data('favoriteopen', '1').popover({
            content: data,
            html: true,
            placement: 'bottom',
        }).popover('toggle')

        jQuery('.toggle-product-favorite-list-' + productId).
            change(function (e) {
                e.stopPropagation()
                var ajaxData = {
                    favoritelist_id: jQuery(this).data('list'),
                    product_id: productId,
                    added: jQuery(this).is(':checked'),
                }
                jQuery.ajax({
                    url: redSHOPB.RSConfig._('SITE_URL') +
                    'index.php?option=com_redshopb&task=shop.ajaxSetFavorite',
                    data: ajaxData,
                    cache: false,
                    type: 'post',
                    beforeSend: function (xhr) {
                    },
                }).done(function (data) {
                    if (ajaxData.added) {
                        redSHOPB.messages.displayMessage(Joomla.JText._(
                            'COM_REDSHOPB_MYFAVORITELIST_PRODUCT_ADDED_SUCESSFULY',
                            'Product added successfully to Favoritelist'),
                            'alert-info', '')
                        jQuery(buttonElement).addClass('added')
                    } else {
                        redSHOPB.messages.displayMessage(Joomla.JText._(
                            'COM_REDSHOPB_MYFAVORITELIST_REMOVED_SUCCESSFULLY',
                            'Product removed from Favoritelist successfully'),
                            'alert-info', '')
                        if (jQuery('.toggle-product-favorite-list-' +
                                productId + ':checked', '').length == 0) {
                            jQuery(buttonElement).removeClass('added')
                        }
                    }
                })
            })

        jQuery('#list-new-' + productId).on('keyup', function (e) {
            e.stopPropagation()
            if (jQuery(this).val().trim() == '') {
                jQuery('#list-new-button-' + productId).addClass('disabled')
            }
            else {
                jQuery('#list-new-button-' + productId).removeClass('disabled')
            }
        }).on('change', function (e) {
            e.stopPropagation()
        })

        jQuery('#list-new-button-' + productId).on('click', function (e) {
            var name = jQuery('#list-new-' + productId).val().trim()
            if (name == '') {
                return
            }
            var ajaxData = {
                favoritelist_name: name,
                product_id: productId,
            }
            jQuery.ajax({
                url: redSHOPB.RSConfig._('SITE_URL') +
                'index.php?option=com_redshopb&task=shop.ajaxCreateFavorite',
                data: ajaxData,
                cache: false,
                type: 'post',
                beforeSend: function (xhr) {
                },
            }).done(function (data) {
                var favoritelistName = jQuery('#list-new-' + productId).val()
                jQuery(buttonElement).
                    data('favoriteopen', '0').
                    popover('destroy').
                    addClass('added')
                redSHOPB.messages.displayMessage(Joomla.JText._(
                    'COM_REDSHOPB_MYFAVORITELIST_PRODUCT_SUCCESSFULLY_ADDED_TO',
                    'Product Successfully added to') + ' ' + favoritelistName,
                    'alert-info', '')
            })
        })
    })
}

jQuery(document).ready(function () {
    assignAddToFavoriteButtons()

    jQuery('#productCombinations').on('hidden.bs.modal', function () {
        jQuery(this).removeData()
    })

})

jQuery(".redcore ").on('change', function() {
	assignAddToFavoriteButtons()

	jQuery('#productCombinations').on('hidden.bs.modal', function () {
		jQuery(this).removeData()
	})
});