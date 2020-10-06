/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

/**
 * Creating dummy SqueezeBox since we are using BS3 in backend
 * instead of BS2 (which Joomla does). Conflicts happens because
 * of Joomla mootools usage for creating modals.
 * Check AES-1093 for more info.
 */
if (typeof SqueezeBox == 'undefined') {
    var SqueezeBox = {
        initialize : function () {},
        assign : function () {},
        close : function () {}
    }
}

/**
 * Custom behavior for JavaScript dynamic variables
 *
 * Allows you to call redSHOPB.RSConfig._() to get a dynamic JavaScript string pushed in with JText::script() in Joomla.
 */
redSHOPB.RSConfig = {
    configStrings: {},
    '_': function (key, def) {
        return typeof this.configStrings[key.toUpperCase()] !== 'undefined' ? this.configStrings[key.toUpperCase()] : def;
    },
    load: function (object) {
        for (var key in object) {
            this.configStrings[key.toUpperCase()] = object[key];
        }

        return this;
    }
};

redSHOPB.form =
    {
        submit: function (event) {
            var target = redSHOPB.form.getEventTarget(event);
            var form = target.closest('form');

            if (target.attr('data-list')) {
                var checkedBoxs = form.find('input[name="boxchecked"]').val();

                if (checkedBoxs == 0) {
                    alert(Joomla.JText.strings.COM_REDSHOPB_PLEASE_SELECT_ITEM);
                    event.preventDefault();
                    return false;
                }
            }

            var toDoTask = target.attr('data-task');
            form.find('input[name="task"]').val(toDoTask);
            form.submit();
        },

        getData: function (form, task) {
            var taskInput = redSHOPB.form.getInput('task', form);
            var orignialValue = taskInput.val();
            taskInput.val(task);

            var formData = form.serialize();

            taskInput.val(orignialValue);

            return formData;
        },

        /**
         * Get a target from an event escaping any icons
         *
         * @param event
         * @returns {*}
         *
         * @deprecated use getEventTarget instead
         */
        getButtonTarget: function (event) {
            return redSHOPB.form.getEventTarget(event);
        },

        getEventTarget: function (event) {
            var targetElement = event.target || event.srcElement;
            var target = jQuery(targetElement);

            // If the event bubbled up from an icon use the parent
            if (targetElement.tagName == 'I') {
                target = jQuery(targetElement.parentElement);
            }

            return target;
        },

        trackAltered: function (formElement) {
            var form = jQuery(formElement);
            form.addClass('js-altered');
        },

        submitDependencies: function (formElement) {
            var form = jQuery(formElement);
            var saveTask = form.attr('data-save-task');
            var task = jQuery('#' + form.attr('id') + ' input[name ="task"]');
            task.val(saveTask);
            var url = form.attr('action');

            jQuery.post(url, form.serialize());
        },

        getInput: function (name, form) {
            var input = form.find('input[name ="' + name + '"]');

            if (typeof input.val() == 'undefined') {
                form.append('<input type="hidden" name="' + name + '"/>');
                input = form.find('input[name ="' + name + '"]');
            }

            return input;
        },

        clientSupportsFormAttributes: function () {
            var input = document.createElement('input');
            var form = document.createElement('form');
            var formId = 'redshopbTestForm';

            form.id = formId;

            document.body.appendChild(form);
            document.body.appendChild(input);

            input.setAttribute('form', formId);
            var res = !(input.form == null);
            document.body.removeChild(form);
            document.body.removeChild(input);

            return res;
        },

        // temporary form attribute polyfill for IE/Edge form attribute
        forgeFormAttributes: function (formId) {
            var form = jQuery('#' + formId);

            var formInputs = jQuery('input[form="' + formId + '"]');

            formInputs.each(function (index, element) {
                element = jQuery(element);
                element.on('change', function (event) {
                    var targ = redSHOPB.form.getEventTarget(event);
                    var name = element.attr('name');
                    var elementClone = redSHOPB.form.getInput(name, form);
                    elementClone.val(targ.val());
                });

                if (element[0].type == 'hidden' || element.val().length > 0)
                {
                    element.trigger('change');
                }

            });

            var formSelects = jQuery('select[form="' + formId + '"]');

            formSelects.each(function (index, element) {
                element = jQuery(element);
                element.on('change', function (event) {
                    var targ = redSHOPB.form.getEventTarget(event);
                    var selected = targ.find(':selected');
                    var name = element.attr('name');
                    var elementClone = redSHOPB.form.getInput(name, form);
                    elementClone.val(selected.val());
                });

                if (element[0].className.indexOf('hidden') != -1)
                {
                    element.trigger('change');
                }
            });
        }
    };

redSHOPB.messages =
{
    delay : 2000,
    fade : 3000,
    displayMessage: function(message, alertType, modal, doNotFade) {
		if (alertType == "modal") {
			jQuery('body').append(modal);
		}
		else {
			var alertBox = jQuery('#redshopbalertmessage');
			if (!alertType) {
				alertType = 'alert-success';
			}
			if (alertBox.length <= 0) {
				alertBox = jQuery('<div class="alert-box" id="redshopbalertmessage"></div>');
				jQuery('body').append(alertBox);
			}
			var messageHtml = jQuery('<div class="alert ' + alertType + '">' + message + '</div>');
			jQuery(alertBox)
				.prepend(messageHtml);
			if (doNotFade !== true){
				messageHtml
					.delay(redSHOPB.messages.delay)
					.fadeOut(redSHOPB.messages.fade, function(){
						jQuery(this).remove();
					});
            }else{
				messageHtml.prepend('<button type="button" class="close" data-dismiss="alert">&times;</button>');
            }
		}
    },

    displayModalError: function (text) {
        var modalbody = jQuery('.modal-body');
        modalbody.prepend(
            '<div class="row modal-msg">'
            + '<div class="alert alert-error span10 offset1">'
            + '<button type="button" class="close" data-dismiss="alert">'
            + '&times;'
            + '</button>'
            + text
            + '</div>'
            + '</div>');
    }

};

redSHOPB.ajax =
    {
        lastEvent: null,
        searchTimer: 0,
        xhr: null,

        getSettings: function (form, task) {
            return {
                url: form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: redSHOPB.form.getData(form, task)
            };
        },

        search: function (event, resultsCallback, prefix, type) {
            if (typeof prefix == 'undefined') {
                prefix = '#js-product-search';
            }

            if (event.keyCode == 40) {
                var firstResult = jQuery(prefix + '-results:first-child').find('a.js-search_results').first();
                firstResult.focus();
            }

            if (jQuery.inArray(event.keyCode, [16, 17, 18, 20, 30, 37, 38, 39, 40]) != -1) {
                return false;
            }

			if (event.keyCode == 13)
			{
				switch (type)
				{
					case 'quickorder':
						jQuery('#js-product-search-results > div > div > a:nth-child(2)').trigger('click');
						return true;
					default:
						if (redSHOPB.ajax.xhr)
						{
							redSHOPB.ajax.xhr.abort();
						}
						return false;
				}
			}

            var targ = redSHOPB.form.getButtonTarget(event, true);
            jQuery(prefix + '-results').html('').parent('.row-fluid').addClass('hidden');

            // Abort ajax request if it pending and another one starts soon
            if (redSHOPB.ajax.xhr) {
                redSHOPB.ajax.xhr.abort();
            }

            //Fix tab indexes
            jQuery('[tabindex="0"]').each(function (index, element) {
                element = jQuery(element);
                element.attr('tabindex', 1);
            });

            if (targ.val().length < 3) {
                return false;
            }

            this.updateTimer(event, resultsCallback, prefix);
        },

        updateTimer: function (event, resultsCallback, prefix) {
            if (this.searchTimer) {
                clearTimeout(this.searchTimer);
            }
            this.lastEvent = event;
            this.searchTimer = setTimeout(function () {
                redSHOPB.ajax.doSearch(redSHOPB.ajax.lastEvent, resultsCallback, prefix)
            }, 300);
        },

        doSearch: function (event, resultsCallback, prefix) {
            var targ = redSHOPB.form.getButtonTarget(event, true);
            var form = targ.closest('form');

            if (form.attr('data-submitted') === 'true')
            {
                return;
            }

            var settings = {
                // Important don't set view and layout in the ajax search url for avoid hidden redirect for user
                // without impersonation and show notice instead
                url: redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb',
                type: 'POST',
                dataType: 'json',
                data: redSHOPB.form.getData(form, 'shop.ajaxSearch'),
                beforeSend: function () {
                    form.find('input[name="search"]').addClass('loadingTextInput');
                }
            };

            redSHOPB.ajax.xhr = jQuery.ajax(settings)
                .done(function (data, textStatus, jqXHR) {
                    jQuery(prefix + '-results').html(data.html).parent('.row-fluid').removeClass('hidden');
                    jQuery('.js-search_results').on('click', resultsCallback);
                    jQuery('.js-search_results').on('keydown', redSHOPB.ajax.resultNavigate);
                    redSHOPB.ajax.xhr = null;

                    // Executes function for post-processing search results
                    if (jQuery('.js-search_results').data('post-process-results')) {
                        eval(jQuery('.js-search_results').data('post-process-results'));
                    }
                })
                .always(function (data, textStatus, jqXHR) {
                    form.find('input[name="search"]').removeClass('loadingTextInput');
                });
        },

        updateContent: function (pageWrapper, content) {

            if (pageWrapper.attr('data-content-behavior') == 'append') {
                pageWrapper.find("div#redshopbPaginationLoadMore").remove();
                pageWrapper.append(content);

                return true;
            }

            pageWrapper.html(content)
        },

        resultNavigate: function (event) {
            var targ = redSHOPB.form.getButtonTarget(event, true);

            if (event.keyCode == 38) {
                event.stopPropagation();
                targ.prev('a.js-search_results').focus();
            }

            if (event.keyCode == 40) {
                event.stopPropagation();
                targ.next('a.js-search_results').focus();
            }
        },

		generateCsvFile: function(view, table, downloadUrl) {
            var values = [];

            jQuery.each(
                jQuery(table + " input[name='cid[]']:checked"),
                function () {
                    values.push(jQuery(this).val());
                }
            );

            jQuery.ajax({
                url: 'index.php?option=com_redshopb&view='+view+'&task='+view+'.ajaxGenerateCsvFile',
                type: 'POST',
                dataType: 'text',
                data: {result:JSON.stringify(values)},
                success: function(data)
                {
                    location.href=downloadUrl + '&data=' + data;
                }
            });
		}
    };

redSHOPB.fields = {

    displayMessage: function (message, alertType) {
        var alertBox = jQuery('#redshopbalertmessage');
        if (!alertType) {
            alertType = 'alert-success';
        }
        var messageHtml = '<div class="alert ' + alertType + '">' + message + '</div>';

        if (alertBox.length <= 0) {
            alertBox = jQuery('<div class="alert-box" id="redshopbalertmessage"></div>');
            jQuery('body').append(alertBox);
        }
        jQuery(alertBox)
            .html(messageHtml)
            .show()
            .delay(2000)
            .fadeOut(3000);
    },

    displayModalError: function (text) {
        var modalbody = jQuery('.modal-body');
        modalbody.prepend(
            '<div class="row modal-msg">'
            + '<div class="alert alert-error span10 offset1">'
            + '<button type="button" class="close" data-dismiss="alert">'
            + '&times;'
            + '</button>'
            + text
            + '</div>'
            + '</div>');
    },

    adjustDefault: function (defaultValue, text) {
        if (defaultValue != 0) {
            jQuery('td[data-name = "default"]').text(text);
        }
    },

    getTrTarget: function (event) {
        var target = jQuery(event.target.parentElement.parentElement);
        if (event.target.tagName == 'I') {
            target = jQuery(event.target.parentElement.parentElement.parentElement);
        }

        return target
    },

    setFormId: function (value) {
        jQuery('#modalAdminForm :input[name = "jform[id]"]').val(value);
    },

    cleanUpForm: function () {
        jQuery('.modal-msg').remove();
        redSHOPB.fields.setFormId('');
        jQuery('#modalAdminForm label[for = "jform_default1"]').trigger('click');
        jQuery('#modalAdminForm').trigger('reset');
    },

    modalSubmit: function (event) {
        redSHOPB.fields.setFormId('');

        var form = jQuery('#modalAdminForm');
        var url = form.attr('action') + '&task=field_value.ajaxStore';
        var data = form.serialize();

        jQuery.post(url, data).done(function (data, textStatus, jqXHR) {
            var tableBody = jQuery('#fieldValuesTable');
            var record = jQuery.parseJSON(jqXHR.responseText);

            redSHOPB.fields.adjustDefault(record.default, record.defaultsText);
            tableBody.append(record.html);

            jQuery('#fieldValueModal').modal('toggle');
            redSHOPB.fields.cleanUpForm();
            redSHOPB.fields.displayMessage(record.msg, 'alert-success');

        }).fail(function (jqXHR, textStatus, errorThrown) {
            redSHOPB.fields.displayModalError(jqXHR.responseText);
        });
    },

    modalDelete: function (event) {
        var targ = redSHOPB.fields.getTrTarget(event);

        var form = jQuery('#modalAdminForm');
        redSHOPB.fields.setFormId(targ.attr('data-id'));

        var url = form.attr('action');
        var data = form.serialize() + '&task=field_value.ajaxDelete';

        jQuery.post(url, data).done(function (data, textStatus, jqXHR) {
            targ.remove();

            redSHOPB.fields.cleanUpForm();
            redSHOPB.fields.displayMessage(jqXHR.responseText, 'alert-success');
        }).fail(function (jqXHR) {
            redSHOPB.fields.displayMessage(jqXHR.responseText, 'alert-error');
        });
    },

    modalEdit: function (event) {
        redSHOPB.fields.cleanUpForm();

        var targ = redSHOPB.fields.getTrTarget(event);
        jQuery('#modalAdminForm :input[name = "jform[name]"]').val(targ.attr('data-name'));
        jQuery('#modalAdminForm :input[name = "jform[value]"]').val(targ.attr('data-value'));
        redSHOPB.fields.setFormId(targ.attr('data-id'));

        var defaultName = 'jform_default1';
        if (targ.attr('data-default') == 1) {
            defaultName = 'jform_default0';
        }

        jQuery('#modalAdminForm label[for = "' + defaultName + '"]').trigger('click');
        jQuery('#modalSubmitButton').attr('onclick', 'redSHOPB.fields.modalUpdate();');
        jQuery('#fieldValueModal').modal('toggle');
    },

    modalUpdate: function () {
        var form = jQuery('#modalAdminForm');

        var url = form.attr('action') + '&task=field_value.ajaxStore';
        var data = form.serialize();

        jQuery.post(url, data).done(function (data, textStatus, jqXHR) {
            var record = jQuery.parseJSON(jqXHR.responseText);

            redSHOPB.fields.adjustDefault(record.default, record.defaultsText);
            jQuery('#fieldValuesTable tr[data-id ="' + record.id + '"]').replaceWith(record.html);

            jQuery('#fieldValueModal').modal('toggle');
            jQuery('#modalSubmitButton').attr('onclick', 'redSHOPB.fields.modalSubmit();');

            redSHOPB.fields.cleanUpForm();
            redSHOPB.fields.displayMessage(record.msg, 'alert-success');

        }).fail(function (jqXHR, textStatus, errorThrown) {
            redSHOPB.fields.displayModalError(jqXHR.responseText);
        });
    },

    scopeAjaxUpdate: function (controller, inputName, ajaxMethod) {
        var form = jQuery('#adminForm');
        var scope = jQuery('#jform_scope').val();

        var task = jQuery('#adminForm :input[name ="task"]');

        var taskVal = controller + '.' + ajaxMethod;
        task.val(taskVal);

        var url = form.attr('action');
        var data = form.serialize();

        task.val('');

        jQuery.post(url, data).done(function (data, textStatus, jqXHR) {
            console.log(jQuery('#jform_' + inputName));
            jQuery('#jform_' + inputName).parent('.controls').html(jqXHR.responseText);
            jQuery('#jform_' + inputName).chosen();

        }).fail(function (jqXHR) {
            redSHOPB.fields.displayModalError(jqXHR.responseText);
        });
    }
};

redSHOPB.companies = {

    refreshPriceAndDiscountGroups: function (event) {
        var form = jQuery(event.target.form);

        var task = jQuery('input[name = "task"]');
        task.val('company.ajaxRefreshPriceGroups');
        var url = form.attr('action') + '?layout=edit';
        var data = form.serialize();
        task.val('');

        jQuery.post(url, data).done(function (data, textStatus, jqXHR) {
            var result = jQuery.parseJSON(jqXHR.responseText);

            jQuery('#jform_price_group_ids').parent('.controls').html(result.price_group_ids);
            jQuery('#jform_price_group_ids').chosen();

            jQuery('#jform_customer_discount_ids').parent('.controls').html(result.customer_discount_ids);
            jQuery('#jform_customer_discount_ids').chosen();

        }).fail(function (jqXHR) {
            redSHOPB.fields.displayModalError(jqXHR.responseText);
        });
    }
};

redSHOPB.products = {
    url: null,
    data: null,
    initTab: null,
    executingAjax: {},

    unBlockSaveButtons: function (functionName) {
        delete redSHOPB.products.executingAjax[functionName];
        if (Object.keys(redSHOPB.products.executingAjax).length < 1) {
            jQuery('.prevented')
                .removeClass('disabled prevented')
                .prop({'disabled': null});
        }
    },

    init: function (url, data, tags, categories, mainCategory, id, activeTab) {
        redSHOPB.products.url = url;
        redSHOPB.products.data = data;
        jQuery('[onclick^="Joomla.submitbutton(\'product.apply\')"],[onclick^="Joomla.submitbutton(\'product.save2new\')"],[onclick^="Joomla.submitbutton(\'product.save\')"]')
            .removeClass('hide').addClass('disabled prevented').prop({'disabled': 'disabled'});

        redSHOPB.products.getMainCategory(mainCategory, redSHOPB.products.unBlockSaveButtons);
        redSHOPB.products.getCompanyCategories(categories, redSHOPB.products.unBlockSaveButtons);
        redSHOPB.products.getCompanyTags(tags, redSHOPB.products.unBlockSaveButtons);

        if (id != 0) {
            activeTab = jQuery(activeTab);
            var id = activeTab.attr('id');
            // if its a non-ajax tab, then we start from compositions
            if (id == 'details'
                || id == 'images'
                || id == 'fields') {
                id = 'compositions';
            }

            redSHOPB.products.initTab = id;
            redSHOPB.products.loadTab(redSHOPB.products.initTab);
        }

        jQuery('#jform_company_id').on('change', function () {
            redSHOPB.products.getMainCategory([]);
            redSHOPB.products.getCompanyCategories([]);
            redSHOPB.products.getCompanyTags([]);
        });

        jQuery('div.image-edit-area-alert').on('click', '.close', function (e) {
            jQuery('div.image-edit-area-alert').css({'display': 'none'});
        });

        jQuery('#images')
            .on('click', '.product-image-toggle', function (e) {
                redSHOPB.products.imageToggle(this);
            })
            .on('click', '.product-image-edit, .product-image-add', function (e) {
                redSHOPB.products.imageEdit(this);
            })
            .on('click', '.product-image-remove', function (e) {
                redSHOPB.products.imageDelete(this);
            })
            .on('click', 'button#product-image-sync', function (e) {
                var progressLog = jQuery('div#progress-log');
                progressLog.html('');

                jQuery('div#divSyncImage').removeClass('hide');
                redSHOPB.products.imageSync(20, progressLog);
            });
    },

    getMainCategory: function (mainCategory, callback) {
        var companyId = jQuery('#jform_company_id').val();

        var settings = {
            url: redSHOPB.products.url + '&task=product.ajaxMainCategory&company_id=' + companyId,
            type: 'POST',
            cache: false,
            dataType: 'html',
            beforeSend: function (jqXRH) {
                jQuery('#redshopb-categories-category_id').html('');
                jQuery('#redshopb-categories-category_id-loading').show();
                redSHOPB.products.executingAjax['getMainCategory'] = true;
            }
        };

        settings.data = jQuery.extend({category_id: mainCategory}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            jQuery('#redshopb-categories-category_id-loading').hide();
            var container = jQuery('#redshopb-categories-category_id');
            container.html(data);
            container.find('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});

            if (callback && typeof(callback) === "function") {
                callback('getMainCategory');
            }
        });
    },

    getCompanyCategories: function (categories, callback) {
        var companyId = jQuery('#jform_company_id').val();

        var settings = {
            url: redSHOPB.products.url + '&task=product.ajaxCompanyCategories&company_id=' + companyId,
            type: 'POST',
            cache: false,
            dataType: 'html',
            beforeSend: function (jqXRH) {
                jQuery('#redshopb-categories-categories').html('');
                jQuery('#redshopb-categories-categories-loading').show();
                redSHOPB.products.executingAjax['getCompanyCategories'] = true;
            }
        };

        settings.data = jQuery.extend({categories: categories}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            jQuery('#redshopb-categories-categories-loading').hide();
            var container = jQuery('#redshopb-categories-categories');
            container.html(data);
            container.find('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});

            if (callback && typeof(callback) === "function") {
                callback('getCompanyCategories');
            }
        });
    },

    getCompanyTags: function (tags, callback) {
        var companyId = jQuery('#jform_company_id').val();

        var settings = {
            url: redSHOPB.products.url + '&task=product.ajaxCompanyTags&company_id=' + companyId,
            type: 'POST',
            cache: false,
            dataType: 'html',
            beforeSend: function (jqXRH) {
                jQuery('#redshopb-tags').html('');
                jQuery('#redshopb-tags-loading').show();
                redSHOPB.products.executingAjax['getCompanyTags'] = true;
            }
        };

        settings.data = jQuery.extend({tags: tags}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            jQuery('#redshopb-tags-loading').hide();
            var container = jQuery('#redshopb-tags');
            container.html(data);
            container.find('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});

            if (callback && typeof(callback) === "function") {
                callback('getCompanyTags');
            }
        });
    },

    loadTab: function (tabName) {
        var tab = jQuery('#' + tabName);

        var settings =
            {
                url: redSHOPB.products.url + '&task=product.ajax' + tabName,
                type: 'POST',
                dataType: 'json',
                data: redSHOPB.products.data,
                beforeSend: function (jqXRH) {
                    tab.addClass('opacity-40');
                }
            };

        var form = tab.find('form');

        if (form.length != 0) {
            settings.url = form.attr('action');
            var task = form.find('input[name="task"]');
            task.val('product.ajax' + tabName);
            settings.data = form.serialize();
            task.val('');
        }

        jQuery.ajax(settings).fail(function (jqXHR, textStatus, errorThrown) {
            data = jQuery.parseJSON(jqXHR.responseText);
            tab.find('.tab-content').html(data.html);

        }).done(function (data) {
            tab.find('.tab-content').html(data.html);
            tab.find('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
            tab.find('.hasTooltip').tooltip({
                "animation": true, "html": true, "placement": "top",
                "selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false
            });

            var fooConfig = {
                sort: false,
                paginate: false,
                breakpoints: {
                    phone: 480,
                    tablet: 768,
                    default: rsbftDefault
                }
            };

            if (tabName == 'prices') {
                fooConfig.breakpoints.phone = 600;
            }

            if (tabName == 'tableprices') {
                tab.find('.js-stools-container-filters').show();
            }

            tab.find('.js-redshopb-footable').footable(fooConfig);

            var flexSliderConfig = {
                slideshow: false,
                directionNav: false,
                animation: 'slide',
                animationLoop: false
            };

            tab.find('.flexslider').flexslider(flexSliderConfig);

            tab.find('form[data-save-task]').on('change', function () {
                redSHOPB.products.trackAltered(this);
            });

            // Reselect the form
            form = tab.find('form');
            form.find('input[name="task"]').val('');

            form.find('select[onchange][name^="list["]').attr('onchange', 'redSHOPB.products.updateTab(event);');

            form.find('select[name^="filter["]').attr('onchange', '').attr('onchange', 'redSHOPB.products.updateTab(event);');

            form.on('submit', function () {
                form = jQuery(this);
                var toDoTask = form.find('input[name="task"]').val();

                if (toDoTask == '') {
                    var tabContent = form.parents('div.tab-pane');
                    tabContent.addClass('opacity-40');

                    redSHOPB.products.loadTab(tabContent.attr('id'));
                    return false;
                }

                // If we get this far, then we are leaving the form so make sure we
                // don't have any dependencies

                var alteredForms = jQuery('form.js-altered');

                if (alteredForms.length != 0) {
                    if (confirm(Joomla.JText.strings.COM_REDSHOPB_PRODUCT_UNSAVED_CHANGES_WARNING) == false) {
                        return false;
                    }

                    alteredForms.each(function (index, element) {
                        redSHOPB.products.submitDependencies(element);
                    })
                }
            });

            form.find('ul.pagination-list').find('a').each(function (index, element) {
                element = jQuery(element);
                var values = element.attr('onclick').split(';')[0].split('=');

                var inputName = values[0].split('.')[2];
                var limitstart = values[1];

                element.attr('onclick', '')
                    .attr('href', 'javascript:void(0);')
                    .attr('data-limitstart', limitstart)
                    .attr('data-input-target', inputName)
                    .prop('form', element.closest('form'))
                    .on('click', function (event) {
                        var targ = redSHOPB.form.getEventTarget(event);
                        targ.closest('form')
                            .find('input[name ="' + targ.attr('data-input-target') + '"]')
                            .val(targ.attr('data-limitstart'));

                        redSHOPB.products.updateTab(event);
                    });
            });
        }).always(function () {
            if (tabName == redSHOPB.products.initTab &&
                redSHOPB.products.initTab != 'details') {
                redSHOPB.products.bgTabLoad();
                redSHOPB.products.initTab = 'details';
            }

            tab.removeClass('opacity-40')
        });
    },

    updateTab: function (event) {
        var target = jQuery(event.target);

        var form = jQuery(target.prop('form'));
        form.find('input[name="task"]').val('');

        var tabContent = form.parents('div.tab-pane');
        tabContent.addClass('opacity-40');

        redSHOPB.products.loadTab(tabContent.attr('id'));
    },

    tabSubmit: function (event) {
        var target = redSHOPB.form.getEventTarget(event);
        var form = target.closest('form');

        if (target.attr('data-list')) {
            var checkedBoxs = form.find('input[name="boxchecked"]').val();

            if (checkedBoxs == 0) {
                alert(Joomla.JText.strings.COM_REDSHOPB_PLEASE_SELECT_ITEM);
                return false;
            }
        }

        var toDoTask = target.attr('data-task');
        var task = form.find('input[name="task"]');

        task.val(toDoTask);
        var data = form.serialize();
        task.val('');

        var tabContent = form.parents('div.tab-pane');
        tabContent.addClass('opacity-40');

        jQuery.post(form.attr('action'), data).done(function (data, textStatus, jqXHR) {
            redSHOPB.products.loadTab(tabContent.attr('id'));
        });
    },

    bgTabLoad: function () {
        var tabs = jQuery('[data-ajax-tab-load="true"]');

        tabs.each(function (index, element) {
            element = jQuery(element);
            var target = element.attr('href').substr(1);

            if (target == redSHOPB.products.initTab) {
                return;
            }

            redSHOPB.products.loadTab(target);
        });
    },

    trackAltered: function (formElement) {
        var form = jQuery(formElement);
        form.addClass('js-altered');
    },

    submitDependencies: function (formElement) {
        var form = jQuery(formElement);
        var saveTask = form.attr('data-save-task');
        var task = jQuery('#' + form.attr('id') + ' input[name ="task"]');
        task.val(saveTask);
        var url = form.attr('action');

        jQuery.post(url, form.serialize());
    },

    imageToggle: function (obj) {
        obj = jQuery(obj);
        var imageTab = jQuery('#images');
        var imageId = obj.attr('data-id');
        var imageState = obj.attr('data-state');

        var settings = {
            url: redSHOPB.products.url,
            type: 'POST',
            dataType: 'json',
            beforeSend: function (jqXRH) {
                imageTab.addClass('opacity-40');
            }
        };

        settings.data = jQuery.extend({
            task: 'product.toggleImageState',
            image_id: imageId,
            state: imageState
        }, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            if (data.success) {
                obj.parent('td').parent('tr').html(data.updatedRow);

                editFormId = jQuery('#imageForm #jform_id').val();

                if (editFormId == data.id) {
                    redSHOPB.products.imageEdit(obj);
                }
            }

            imageTab.removeClass('opacity-40');
        });
    },

    imageSave: function (obj) {
        var obj = jQuery(obj);
        var imageTab = jQuery('#images');
        var buttonData = obj.data();
        var progressBar = jQuery('.image-progress .bar');
        var uploader = jQuery('#jform_productImage');

        progressBar.css('width', '0%');

        if (jQuery.isEmptyObject(buttonData) == false) {
            uploader.prop('disabled', !jQuery.support.fileInput)
                .parent().addClass(jQuery.fileInput ? undefined : 'disabled');

            buttonData.submit();
            obj.removeData();
        }
        else {

            var editArea = jQuery('#images-edit-area');
            var settings = {
                url: redSHOPB.products.url + '&task=product.ajaxImageSave',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: jQuery('#imageForm').serializeArray(),
                beforeSend: function (xhr) {
                    imageTab.addClass('opacity-40');
                }
            };

            jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
                imageTab.removeClass('opacity-40');
                var alertArea = jQuery('.image-edit-area-alert');
                var imageTable = jQuery('.product-images-table');

                if (data.success) {
                    progressBar.css('width', '100%');
                    alertArea.removeClass('alert-error').addClass('alert-success');

                    editArea.find('#jform_id').val(data.id);
                    editArea.find('#jform_alt').val(data.alt);

                    if (data.image) {
                        editArea.find('.bigThumb').remove();
                        editArea.find('.product-media-manager-container')
                            .prepend('<img src="' + data.image + '" class="bigThumb" />');
                    }

                    var buttonEdit = jQuery('button[data-id="' + data.id + '"]');

                    if (buttonEdit.length) {
                        buttonEdit.closest('tr').html(data.updatedRow);
                    }
                    else {
                        imageTable.removeClass('hide').find('tr:last').after('<tr>' + data.updatedRow + '</tr>');
                    }

                    alertArea.find('h3').html(data.message);
                    alertArea.show();
                }
            });
        }
    },

    imageEdit: function (obj) {
        obj = jQuery(obj);
        var imageTab = jQuery('#images');
        var editArea = jQuery('#images-edit-area');
        var imageId = obj.attr('data-id');

        var settings = {
            url: redSHOPB.products.url,
            type: 'POST',
            dataType: 'html',
            beforeSend: function (jqXRH) {
                imageTab.addClass('opacity-40');
            }
        };

        settings.data = jQuery.extend({task: 'product.ajaxImageEdit', image_id: imageId}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            editArea.html(data).focus();
            editArea.find('select').trigger("liszt:updated");
            editArea.find('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
            redSHOPB.products.prepareUploader();

            jQuery('.product-image-save-button').on('click', function (e) {
                redSHOPB.products.imageSave(this);
            });
            imageTab.removeClass('opacity-40');
        });
    },

    prepareUploader: function () {
        imageTab = jQuery('#images');
        jQuery('#files').html('');

        var settings = {
            url: redSHOPB.products.url + '&task=product.ajaxImageSave',
            dataType: 'json',
            cache: false,
            countExecute: 0,
            autoUpload: false,
            acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
        };

        var uploader = jQuery('#jform_productImage');

        uploader.fileupload()
            .fileupload('destroy')
            .fileupload(settings)
            .on('fileuploadsubmit', function (e, data) {
                imageTab.addClass('opacity-40');
                data.formData = jQuery('#imageForm').serializeArray();
            })
            .on('fileuploadadd', function (e, data) {
                jQuery.each(data.files, function (index, file) {
                    jQuery('#files').html(file.name);
                    jQuery('.product-image-save-button').data(data);
                });
            })
            .on('fileuploadfail', function (e, data) {
                jQuery.each(data.files, function (index, file) {
                    var error = jQuery('<span class="text-danger"/>');
                    jQuery(data.context.children()[index])
                        .append('<br>')
                        .append(error);
                });

                redSHOPB.products.prepareUploader();
            })
            .on('fileuploadprogressall', function (e, data) {
                jQuery('.product-image-save-button').prop('disabled', true);

                var progress = parseInt(data.loaded / data.total * 100, 10);

                jQuery('.image-progress .bar').css('width', progress + '%');
            })
            .on('fileuploaddone', function (e, data) {

                var editArea = jQuery('#images-edit-area');
                var alertArea = jQuery('.image-edit-area-alert');
                var imageTable = jQuery('.product-images-table');

                imageTab.removeClass('opacity-40');

                if (data.countExecute == 0) {
                    if (data.result.success) {
                        alertArea.removeClass('alert-error').addClass('alert-success');

                        editArea.find('#jform_id').val(data.result.id);
                        editArea.find('#jform_alt').val(data.result.alt);

                        editArea.find('.bigThumb').remove();
                        editArea.find('.product-media-manager-container')
                            .prepend('<img src="' + data.result.image + '" class="bigThumb" />');

                        var buttonEdit = jQuery('button[data-id="' + data.result.id + '"]');

                        if (buttonEdit.length) {
                            buttonEdit.closest('tr').html(data.result.updatedRow);
                        }
                        else {

                            imageTable.removeClass('hide').find('tr:last').after('<tr>' + data.result.updatedRow + '</tr>');
                        }
                    }
                    else {
                        alertArea.addClass('alert-error').removeClass('alert-success');
                    }

                    alertArea.find('h3').html(data.result.message);
                    alertArea.show();
                    jQuery('.product-image-save-button').prop('disabled', false);
                }

                data.countExecute++;
                redSHOPB.products.prepareUploader();
            });
    },

    imageDelete: function (obj) {
        obj = jQuery(obj);
        var imageTab = jQuery('#images');
        var editArea = jQuery('#images-edit-area');
        var imageId = obj.attr('data-id');

        var settings = {
            url: redSHOPB.products.url,
            type: 'POST',
            dataType: 'json',
            beforeSend: function (jqXRH) {
                imageTab.addClass('opacity-40');
            }
        };

        settings.data = jQuery.extend({task: 'product.ajaxImageDelete', image_id: imageId}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            if (data.success) {
                obj.closest('tr').remove();
                editFormId = jQuery('#imageForm #jform_id').val();

                if (editFormId == imageId) {
                    editArea.html('');
                }

                var alertArea = jQuery('.image-edit-area-alert');
                alertArea.removeClass('alert-error').addClass('alert-success');
                alertArea.find('h3').html(data.message);
                alertArea.show();
                imageTab.removeClass('opacity-40');
            }
        });
    },

    imageSync: function (percent, progressLog) {
        var progressImagesSync = jQuery('div#progressImagesSync');

        var settings = {
            url: redSHOPB.products.url,
            type: 'POST',
            dataType: 'json',
            beforeSend: function (xhr) {
                progressImagesSync.find('div.bar').css('width', '0%');
                progressImagesSync.find('div.bar-success').css('width', percent + '%');
                progressImagesSync.addClass('active');
            }
        };

        settings.data = jQuery.extend({task: 'product.ajaxImageSync'}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            var hasErrors = false;

            if (textStatus == 'timeout' || textStatus == 'parseerror') {
                progressLog.append('<span class="label label-important">' + Joomla.JText.strings.COM_REDSHOPB_MEDIA_SYNC_ERROR_TIMEOUT + '</span><br/>');
                hasErrors = true;
            }
            else if (typeof data === 'undefined' || textStatus == 'error') {
                progressLog.append('<span class="label label-important">' + Joomla.JText.strings.COM_REDSHOPB_MEDIA_SYNC_ERROR_APPLICATION_ERROR + '</span><br/>');
                hasErrors = true;
            }
            if (data.messages.length > 0) {
                jQuery(data.messages).each(function (messageIdx, messageData) {
                    progressLog.append('<span class="label label-' + messageData.type_message + '">' + messageData.message + '</span><br/>');

                    if (messageData.type_message == 'important') {
                        hasErrors = true;
                    }
                });
            }

            if (hasErrors
                || data.success == false
                || typeof data.success[0] == 'undefined'
                || typeof data.success[0]['parts'] == 'undefined') {
                if (hasErrors || data.success == false) {
                    var widthProgress = progressImagesSync.width();
                    var widthSuccess = progressImagesSync.find('.bar-success').width();
                    var percentError = Math.floor(100 * (widthProgress - widthSuccess) / widthProgress);
                    progressImagesSync.append('<div class="bar bar-danger" style="width: ' + percentError + '%;"></div>');
                }
                else {
                    progressImagesSync.find('div.bar-success').css('width', '100%');
                }

                progressImagesSync.removeClass('active');
            }
            else {
                var percent = 100 - Math.ceil((100 * data.success[0]['parts']) / data.success[0]['total']);
                redSHOPB.products.imageSync(percent, progressLog);
            }
        });
    },

    saveNewPrice: function (event) {
        var target = redSHOPB.form.getEventTarget(event);

        var form = target.closest('form');
        var id = target.attr('data-id');
        var price = form.find('[name="jform[price_new][' + id + ']"]').val();
        var alpha3 = target.attr('data-currency-alpha');
        var currencyId = target.attr('data-currency-id');
        var sku = target.parents('div.js-price-wrapper').attr('data-sku');

        var settings = {
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json'
        };

        settings.data = jQuery.extend(
            {
                productItemId: id,
                currencyId: currencyId,
                currencyAlpha3: alpha3,
                price: price,
                sku: sku,
                task: 'all_price.ajaxSaveNewPrice'
            },
            redSHOPB.products.data
        );

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            if (data.messageType == 'alert-error') {
                redSHOPB.messages.displayMessage(data.message, data.messageType, '');
                return;
            }

            var divWrapper = target.parents('div.js-price-wrapper');
            divWrapper.html(data.html);
            redSHOPB.products.initPopOver();

            redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            form.removeClass('altered');
        });
    },

    initPopOver: function () {
        jQuery('.hasPopover').popover(
            {
                animation: false,
                trigger: 'hover',
                placement: function (context, source) {
                    var position = jQuery(source).position();
                    var returnVal = 'bottom';

                    if (position.left > 300) {
                        returnVal = "left";
                    }

                    if (position.left < 300) {
                        returnVal = "right";
                    }

                    return returnVal;
                },
                html: true,
                content: function () {
                    return jQuery('#price_description_' + jQuery(this).attr('data-price-id')).html();
                }
            });
    },

    updatePrice: function (event) {
        var target = redSHOPB.form.getEventTarget(event);

        var form = target.closest('form');
        var id = target.attr('data-id');
        var price = jQuery('#jform_price_' + id).val();

        var settings = {
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json'
        };

        settings.data = jQuery.extend({
            id: id,
            price: price,
            task: 'all_price.ajaxChangePrice'
        }, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {
            redSHOPB.messages.displayMessage(data.message, data.messageType, '');
            form.removeClass('altered');
        });
    },

    deletePrice: function (event) {
        var target = redSHOPB.form.getEventTarget(event);

        var form = target.closest('form');
        var id = target.attr('data-id');
        var settings = {
            url: form.attr('action'),
            type: 'POST',
            dataType: 'json',
        };

        settings.data = jQuery.extend({cid: [id], task: 'all_prices.ajaxDelete'}, redSHOPB.products.data);

        jQuery.ajax(settings).done(function (data, textStatus, jqXHR) {

            if (data.messageType == 'error') {
                redSHOPB.messages.displayMessage(data.message, 'alert-error', '');

                return;
            }

            var priceRecords = target.parents('td').find('div.js-price-wrapper');

            if (priceRecords.length != 1) {
                target.parents('div.js-price-wrapper').remove();
                redSHOPB.messages.displayMessage(data.message, 'alert-success', '');
                return;
            }

            var input = form.find('input[name="jform[price][' + id + ']"]');
            input.val('');
            input.attr('name', 'jform[price_new][' + id + ']');
            input.popover('destroy');

            var alpha3 = priceRecords.find('span.add-on').text();

            var btnGroup = target.parents('div.js-record-controls');
            btnGroup.replaceWith('<a href="javascript:void(0);" class="btn" data-action="saveNewPrice" data-id="'
                + id + '" data-currency-alpha="' + alpha3 + '" data-currency-id="' + form.find('input[name="jform[default_currency_id]"]').val() + '"'
                + ' onclick="redSHOPB.products.saveNewPrice(event);"><i class="icon-save"></i></a>');

            jQuery('#price_description_' + id).remove();
            redSHOPB.messages.displayMessage(data.message, 'alert-success', '');
        });
    }
};

redSHOPB.offer = {
    refreshTable: function (event, operation)
    {
        if (operation == 'deleteRow')
        {
            var targ      = redSHOPB.form.getEventTarget(event);
            var currentTr = targ.closest('tr');

            currentTr.remove();

            var form = jQuery('form#productsOfferForm');
        }
        else if (operation == 'changeQuantity')
        {
            var targ = redSHOPB.form.getEventTarget(event);
            var form = targ.closest('form');
        }
        else if (operation == 'changeDiscount')
        {
            var targ                = redSHOPB.form.getEventTarget(event);
            var form                = targ.closest('form');
            var targetRow           = targ.closest('tr');
            var targetProductId     = targetRow.find('.productIdField').val();
            var targetProductItemId = targetRow.find('.productItem').val();
            var targetDiscount      = targetRow.find('.productDiscount').val();
            var targetTypeDiscount  = targetRow.find('.productDiscountType').val();

            if (typeof targetProductItemId === 'undefined')
            {
                targetProductItemId = 0;
            }
        }

        var formName = form.attr('name');
        var token    = jQuery('#token input');
        var settings = {
            'offer_id'         : jQuery('#offer_id').val(),
            'task'             : 'offer.ajaxRefreshProducts',
            'form_name'        : formName,
            'content'          : [],
            'changed_discount' : []
        };

        settings[token.attr("name")] = token.val();

        var rows = form.find('table tbody tr');

        rows.each(function(){
            let quantity   = jQuery(this).find('.productQuantity').val();
            let productId     = jQuery(this).find('.productIdField').val();
            let productItemId = jQuery(this).find('.productItem').val();
            let discount      = jQuery(this).find('.productDiscount').val();
            let discountType  = jQuery(this).find('.productDiscountType').val();

            if (typeof productItemId === 'undefined')
            {
                productItemId = 0;
            }

            let rowData = {
                productId     : productId,
                productItemId : productItemId,
                quantity      : quantity,
                discount      : discount,
                discountType  : discountType
            };

            let perRowAttrMenu = jQuery(this).find('select.dropdownPriceCondition.productItem');

            // If we are on products tab and current row has an attributes drop menu
            if (perRowAttrMenu.length === 1)
            {
                let selectedProductItemId = perRowAttrMenu.find('option:selected').val();
                rowData['selectedProductItemId'] = selectedProductItemId
            }

            settings.content.push(rowData);
        });

        if (operation == 'changeDiscount')
        {
            settings.changed_discount.push({
                productId     : targetProductId,
                productItemId : targetProductItemId,
                discount      : targetDiscount,
                discountType  : targetTypeDiscount
            });
        }

        if (formName == 'productsOfferForm')
        {
            var tabName = 'offerproducts';
        }
        else if (formName == 'productsForm')
        {
            var tabName = 'products';
        }

        jQuery
            .ajax({
                url: form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: settings,
                beforeSend: function () {
                    jQuery('.spinner-' + tabName + '-content').show();
                    jQuery('#offerTabs').addClass('opacity-40');
                }
            })
            .done(function(data, textStatus, jqXHR)
            {
                var wrapper = jQuery('.' + data.tabName + '-content');
                jQuery('.spinner-' + data.tabName + '-content').hide();
                jQuery('#offerTabs').removeClass('opacity-40');
                wrapper.html(data.body);
                jQuery('select').chosen();
                jQuery('.chzn-search').hide();
                jQuery('.hasTooltip').tooltip({
                    "animation": true, "html": true, "placement": "top",
                    "selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false
                });

                wrapper.find('.productQuantity').on('change', function (event)
                {
                    redSHOPB.offer.refreshTable(event, 'changeQuantity');
                });
                wrapper.find('.offer-item-remove').on('click', function (event)
                {
                    redSHOPB.offer.refreshTable(event, 'deleteRow');
                });

                jQuery('.save-offer-items').removeClass('disabled');
            })
            .fail(function(jqXHR, textStatus, errorThrown)
            {
                console.dir(errorThrown);
            })
            .always(function(data, textStatus, jqXHR)
            {
            });
    }
}

redSHOPB.ajaxTabs =
    {
        token: null,
        initTab: null,
        flexSliderUse: false,

        init: function (token, flexSliderUse) {
            redSHOPB.ajaxTabs.token = token;
            redSHOPB.ajaxTabs.flexSliderUse = flexSliderUse;

            var activeTab = jQuery('ul.nav-tabs li.active a[data-toggle="tab"]');

            if (activeTab.attr('data-ajax-tab-load') == undefined) {
                activeTab = jQuery(jQuery('[data-ajax-tab-load="true"]')[0]);
            }

            redSHOPB.ajaxTabs.initTab = activeTab.attr('href').substr(1);
            redSHOPB.ajaxTabs.loadTab(redSHOPB.ajaxTabs.initTab);
        },

        loadTab: function (tabName) {
            var tab = jQuery('#' + tabName);
            var url = tab.attr('data-url');
            var loadTask = tab.attr('data-load-task');

            var settings =
                {
                    url: tab.attr('data-url'),
                    type: 'POST',
                    dataType: 'json',
                    data: redSHOPB.ajaxTabs.token + '&task=' + tab.attr('data-load-task') + '&tab=' + tabName,
                    beforeSend: function (jqXRH) {
                        tab.addClass('opacity-40');
                    }
                };

            var form = tab.find('form');

            if (form.length != 0) {
                settings.url = form.attr('action');
                var task = form.find('input[name="task"]');
                task.val(tab.attr('data-load-task'));
                settings.data = form.serialize();
                task.val('');
            }

            jQuery.ajax(settings).fail(function (jqXHR, textStatus, errorThrown) {
                data = jQuery.parseJSON(jqXHR.responseText);
                tab.find('.ajax-content').html(data.html);

            }).done(function (data) {
                // Initialize any fields
                tab.find('.ajax-content').html(data.html);
                tab.find('select').chosen({"disable_search_threshold": 10, "allow_single_deselect": true});
                tab.find('.hasTooltip').tooltip({
                    "animation": true, "html": true, "placement": "top",
                    "selector": false, "title": "", "trigger": "hover focus", "delay": 0, "container": false
                });

                var fooConfig = {
                    sort: false,
                    paginate: false,
                    breakpoints: {
                        phone: 480,
                        tablet: 768,
                        default: rsbftDefault
                    }
                };

                if (tabName == 'Prices') {
                    fooConfig.breakpoints.phone = 600;
                }

                if (tabName == 'TablePrices') {
                    tab.find('.js-stools-container-filters').show();
                }

                tab.find('.js-redshopb-footable').footable(fooConfig);

                if (redSHOPB.ajaxTabs.flexSliderUse) {
                    var flexSliderConfig = {
                        slideshow: false,
                        directionNav: false,
                        animation: 'slide',
                        animationLoop: false
                    };

                    tab.find('.flexslider').flexslider(flexSliderConfig);
                }

                tab.find('form[data-save-task]').on('change', function () {
                    redSHOPB.form.trackAltered(this);
                });

                // Reselect the form
                form = tab.find('form');
                form.find('input[name="task"]').val('');
                form.find('select[onchange][name^="list["]').attr('onchange', 'redSHOPB.ajaxTabs.updateTab(event);');
                form.find('select[name^="filter["]').attr('onchange', '').attr('onchange', 'redSHOPB.ajaxTabs.updateTab(event);');

                form.on('submit', function () {
                    form = jQuery(this);
                    var toDoTask = form.find('input[name="task"]').val();

                    if (toDoTask == '') {
                        var tabContent = form.parents('div.tab-pane');
                        tabContent.addClass('opacity-40');

                        redSHOPB.ajaxTabs.loadTab(tabContent.attr('id'));
                        return false;
                    }

                    // If we get this far, then we are leaving the form so make sure we
                    // don't have any dependencies

                    var alteredForms = jQuery('form.js-altered');

                    if (alteredForms.length != 0) {
                        if (confirm(Joomla.JText.strings.COM_REDSHOPB_PRODUCT_UNSAVED_CHANGES_WARNING) == false) {
                            return false;
                        }

                        alteredForms.each(function (index, element) {
                            redSHOPB.form.submitDependencies(element);
                        });
                    }
                });
            }).always(function (data, textStatus, jqXHR) {
                if (tabName == redSHOPB.ajaxTabs.initTab
                    && redSHOPB.ajaxTabs.initTab != 'LOADED') {
                    redSHOPB.ajaxTabs.bgTabLoad();
                    redSHOPB.ajaxTabs.initTab = 'LOADED';
                }

                tab.removeClass('opacity-40');
            });
        },

        bgTabLoad: function () {
            var tabs = jQuery('[data-ajax-tab-load="true"]');

            tabs.each(function (index, element) {
                element = jQuery(element);
                var target = element.attr('href').substr(1);

                if (target == redSHOPB.ajaxTabs.initTab) {
                    return;
                }

                redSHOPB.ajaxTabs.loadTab(target);
            });
        },

        updateTab: function (event) {
            var target = jQuery(event.target);

            var form = jQuery(target.prop('form'));
            form.find('input[name="task"]').val('');

            var tabContent = form.parents('div.tab-pane');
            tabContent.addClass('opacity-40');

            redSHOPB.ajaxTabs.loadTab(tabContent.attr('id'));
        }
    };

redSHOPB.categories =
    {
        init: function () {
            var selects = jQuery('#adminForm select').not(function (index, element) {
                return jQuery(element).hasClass('dropDownAttribute') || jQuery(element).parent('.modalVariants');
            });

            if (selects.length > 0)
            {
                jQuery(document).on('change', selects, function (event) {
                    redSHOPB.categories.submit(event);
                });
            }

            jQuery(document).on('click', '.redshopb-shop-category-show a', function (event) {
                var element = jQuery(this);

                element.attr('data-location', element.attr('href'));
                element.attr('href', 'javascript:void(0);');
                redSHOPB.categories.updatePage(event);
            });
        },

        updatePage: function (event) {
            var targ = jQuery(redSHOPB.categories.getTargetLink(event));

            var location = targ.attr('data-location');
            var taskString = '&task=shop.ajaxDisplayCategories';

            if (location.indexOf('?') == -1) {
                taskString = '?' + taskString;
            }

            var url = location + taskString;

            var container = jQuery(event.target).closest('.redcore');

            jQuery.get(url, function (data) {
                container.replaceWith(data);
				/** global: history */
                history.pushState({'page': 'page 2', 'url': location}, 'page 2', location);
                jQuery('#adminForm select.chzn').chosen({
                    "disable_search_threshold": 10,
                    "allow_single_deselect": true
                });
            });
        },

        getTargetLink: function (event) {
            if (event.target.tagName == 'A') {
                return event.target;
            }

            var targ = jQuery(event.target);

            return targ.closest('a');
        },

        submit: function (event) {

            var targ = jQuery(event.target);
            var form = targ.closest('form');

            var task = form.find('input[name="task"]');
            task.val('shop.ajaxDisplayCategories');

            var settings = {
                url: form.attr('action'),
                type: 'POST',
                dataType: 'html',
                data: form.serialize()
            };

            var container = jQuery('.redcore');
            container.addClass('opacity-40');

            jQuery.ajax(settings).done(function (data) {
                container.replaceWith(data);
                jQuery('#adminForm select.chzn').chosen({
                    "disable_search_threshold": 10,
                    "allow_single_deselect": true
                });
            });
        }
    };

redSHOPB.carts = {
    token: null,
    messageSuccess: null,
    messageWarning: null,
    checkoutPage: null,
    cartId: null,

    init: function (token, messageSuccess, messageWarning, checkoutPage, cartId) {
        redSHOPB.carts.token = token;
        redSHOPB.carts.messageSuccess = messageSuccess;
        redSHOPB.carts.messageWarning = messageWarning;
        redSHOPB.carts.checkoutPage = checkoutPage;
        redSHOPB.carts.cartId = cartId;

        jQuery(".btn-remove-saved-cart").click(function (event) {
            event.preventDefault();

            var cartId = jQuery(this).attr("data-id");
            jQuery('.img-loading-cart-' + cartId).css('visibility', 'visible');

            // Perform ajax request for remove saved cart
            jQuery.ajax({
                url: redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=cart.ajaxRemoveCart',
                cache: false,
                type: "POST",
                dataType: "JSON",
                data: redSHOPB.carts.token + '=1&cartId=' + cartId
            })
                .success(function (data) {
                    var messageContainer = jQuery('#system-message-container');
                    if (data.status == '1') {
                        var msg = '<div class=\"alert alert-success\"><a class=\"close\" data-dismiss=\"alert\">×</a><h4 class=\"alert-heading\">' + redSHOPB.carts.messageSuccess + '</h4><div><p>' + data.msg + '</p></div></div>';
                        messageContainer.html(msg);

                        jQuery('#savedCartsTable tr#row-' + cartId).hide('slow', function () {
                            jQuery('#savedCartsTable tr#row-' + cartId).remove();
                        });
                    }
                    else {
                        var msg = '<div class=\"alert alert-error\"><a class=\"close\" data-dismiss=\"alert\">×</a><h4 class=\"alert-heading\">' + redSHOPB.carts.messageWarning + '</h4><div><p>' + data.msg + '</p></div></div>';
                        messageContainer.html(msg);
                        jQuery('.img-loading-cart-' + cartId).css('visibility', 'hidden');
                    }

                    jQuery('html, body').animate({
                        scrollTop: messageContainer.offset().top
                    }, 1000);
                });
        });

        // Checkout cart process
        jQuery(".btn-checkout-saved-cart").unbind('click');
        jQuery(".btn-checkout-saved-cart").click(function (event) {
            event.preventDefault();

            // Try to use function checkout from redSHOPB Cart object.
            if (redSHOPB.cart.checkOutSavedCart != undefined) {
                redSHOPB.cart.checkOutSavedCart(event);
            }
            else {
                var cartId = jQuery(this).attr("data-id");
                jQuery('.img-loading-cart-' + cartId).css('visibility', 'visible');

                // Perform ajax request for remove saved cart
                jQuery.ajax({
                    url: redSHOPB.RSConfig._('SITE_URL') + 'index.php?option=com_redshopb&task=cart.ajaxCheckoutCart',
                    cache: false,
                    type: 'POST',
                    data: redSHOPB.carts.token + '=1&cartId=' + cartId
                })
                    .success(function (data) {
                        if (parseInt(data) > 0) {
                            window.location.href = redSHOPB.carts.checkoutPage;
                        }
                    });
            }
        });
    }
};

redSHOPB.empty = function (value) {
    if (typeof value == 'undefined'
        || value == ''
        || parseInt(value) == 0
        || value == false) {
        return true;
    }

    return false;
};

redSHOPB.hasAttr = function (object, attrName) {
    var attr = object.attr(attrName);
    return (typeof attr !== typeof undefined && attr !== false);
};

redSHOPB.forgeEvent = function () {
    document.createEventObject()
};
// Vanir Modals


// Vanir add to cart Modal.
jQuery(document).on('click', '#addToCartModalContent .close', function(){
    jQuery("#addToCartModalContent").remove();
    jQuery("#addToCartModal").remove();
});
jQuery(document).on('click', '#addToCartModal', function(){
    jQuery("#addToCartModalContent").remove();
    jQuery("#addToCartModal").remove();
});
