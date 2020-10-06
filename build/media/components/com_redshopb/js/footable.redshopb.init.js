var fooTableTable;
var fooTableObjects = [];
var fooTableCheckedPhoneTablet = false;

if (!rsbftTablet)
    var rsbftTablet = 768;
if (!rsbftPhone)
    var rsbftPhone = 660;
if (!rsbftDefault)
    var rsbftDefault = 1024;
if (rsbftPagination == undefined)
    var rsbftPagination = false;
if (!rsbftSort)
    var rsbftSort = false;
if (!fooTableMonitorBreakpoints)
    var fooTableMonitorBreakpoints = false;
if (!fooTableClass)
    var fooTableClass = '.js-redshopb-footable';


function initFootableRedshopb() {
    fooTableCheckedPhoneTablet = false;
    fooTableTable = jQuery(fooTableClass);

    fooTableTable.each(function(idx, elem){
        var skipFootable = false;

        for (var i = 0; i < fooTableObjects.length; i++)
        {
            if (jQuery(fooTableObjects[i].table).html() == jQuery(elem).html())
            {
                skipFootable = true;
                break;
            }
        }

        if (!skipFootable)
        {
            jQuery(elem).footable({
                sort: rsbftPagination,
                paginate: rsbftSort,
                breakpoints: {
                    phone: rsbftPhone,
                    tablet: rsbftTablet,
                    default: rsbftDefault
                }
            });

            fooTableObjects[fooTableObjects.length] = jQuery(elem).data('footable');
        }
    });

    if (fooTableObjects.length > 0 && fooTableMonitorBreakpoints) {
		jQuery(fooTableObjects).each(function(idx,elem) {
			fooTableCheckPhoneTabletDesktop(elem);
            jQuery(elem.table).on('footable_breakpoint', function () {
				fooTableCheckPhoneTabletDesktop(elem);
            });
		});

    }
}

function fooTableCheckPhoneTabletDesktop(fooTableObj) {
    var mod = false;
    if ((jQuery(fooTableObj.table).hasClass('tablet') || jQuery(fooTableObj.table).hasClass('phone') || jQuery(fooTableObj.table).hasClass('default'))
        && (!jQuery(fooTableObj.table).find('tbody tr')[0] || !jQuery(jQuery(fooTableObj.table).find('tbody tr')[0]).hasClass('footable-detail-show'))) {
        fooTableOpenMobileRows(fooTableObj);
        mod = true;
    }
    if (mod) fooTableRedraw(fooTableObj);
}

function fooTableOpenMobileRows(fooTableObj) {
    jQuery(fooTableObj.table).find('tbody tr').each(function (idx, elem) {
        try {
            if (!jQuery(elem).hasClass('footable-detail-show')) {
                fooTableObj.toggleDetail(elem);
            }
        }
        catch (e) {
        }
    });
}

function fooTableRedraw(fooTableObj) {
	fooTableObj.resize();
	fooTableObj.redraw();
}

jQuery(document).ready(function(){
    initFootableRedshopb();
});
