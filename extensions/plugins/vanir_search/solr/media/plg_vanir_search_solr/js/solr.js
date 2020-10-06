/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

redSHOPB.solr = {
    syncRecords:function(event)
    {
        var targ = redSHOPB.form.getEventTarget(event);
        targ.attr('onclick', '');
        targ.addClass('disabled');

        var form = targ.closest('form');

        var action = form.attr('action');

        form.attr('action', 'index.php?option=com_ajax&group=vanir_search&plugin=SolrSync&format=json&ignoreMessages=0');

        var settings = redSHOPB.ajax.getSettings(form, '');

        jQuery.ajax(settings)
            .done(function(data, textStatus, jqXHR)
            {
                jQuery('#syncRecordsStatus').append(data.body);
                var start = form.find('input[name="start"]');
                start.val(data.nextStep);

                if(data.finished != 1 && typeof data.finished != undefined)
                {
                    return redSHOPB.solr.syncRecords(event);
                }

                targ.removeClass('disabled');
                targ.attr('onclick', 'redSHOPB.solr.syncRecords(event);')
            }).fail(function (jqXHR, textStatus, errorThrown){}).always(function (data, textStatus, jqXHR) {});
    }
};
