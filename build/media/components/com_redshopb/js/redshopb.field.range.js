/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

redSHOPB.fields = window.redSHOPB.fields || {};

redSHOPB.fields.range = {
    editRange: function (i) {
        var hi = document.getElementById(i.target.getAttribute('data-aesec-field-range-edit'));
        var minVal = parseFloat(document.getElementById(hi.getAttribute('data-aesec-field-range-edit-min')).value);
        var maxVal = parseFloat(document.getElementById(hi.getAttribute('data-aesec-field-range-edit-max')).value);

        if (isNaN(minVal)) {
            minVal = '';
        }

        if (isNaN(maxVal)) {
            maxVal = '';
        }

        if (minVal !== '' && maxVal !== '' && minVal > maxVal) {
            maxVal = '';
        }

        if (minVal === '' && maxVal === '')
        {
            hi.value = '';
            return;
        }

        hi.value = minVal + '-' + maxVal;
    }
};

jQuery(function( $ ) {
    $('*[data-aesec-field-range-edit]').each(function () {
        $(this)[0].addEventListener('input', redSHOPB.fields.range.editRange);
    });
});
