(function ($) {
    $(document).ready(function () {
        /** global: redSHOPB */
        $(redSHOPB.shop)
            .on("onShopBeforeAddToCart", function (event, ajaxSettings) {
                ajaxSettings.data.customText = $("#product_custom_text_" + ajaxSettings.data.productId).val();
            });
        /** global: redSHOPB */
        $(redSHOPB.offer)
            .on("onShopBeforeAddToOffer", function (event, ajaxSettings) {
                ajaxSettings.data.customText = $("#product_custom_text_" + ajaxSettings.data.productId).val();
            });
    });
})(jQuery);
