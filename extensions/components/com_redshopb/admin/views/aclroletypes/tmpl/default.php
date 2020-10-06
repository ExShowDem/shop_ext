<?php
/**
 * @package     Aesir.E-Commerce.Frontend
 * @subpackage  Templates
 *
 * @copyright   Copyright (C) 2012 - 2019 Aesir. All rights reserved.
 * @license     GNU General Public License version 2 or later, see LICENSE.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;

RHelperAsset::load('lib/sortable.min.js', 'com_redshopb');
?>

<script type="text/javascript">
	var allowedRules = [];
	var allowedRulesMainCompany = [];
	var allowedRulesCustomers   = [];
	var allowedRulesOwnCompany  = [];
	var allowedRulesCompanies   = [];
	var allowedRulesDepartments = [];
	var boxHeight = 64;

	(function($){
		$(document).ready(function(){
			function incrementActiveTabCounter(number){
				var activeTab = $("#rule_list li.active a span");
				activeTab.text(parseInt(activeTab.text()) + number);
			}
			// Role Type click
			$("#roletypelist li a").click(function(event){
				event.preventDefault();

				$("#roletypelist li.active").removeClass("active");
				$(this).parent().addClass("active");
				$("#roletypelist li:not(.active)").fadeOut('fast');

				$("#roletypealert").fadeOut('fast', function(){
					$("#roletypeform-wrapper").fadeIn('fast');
					$("#permission-wrapper").fadeIn('fast');

					var max = 0;

					$("#permission-wrapper li.grid-item").each(function(index){
						$(this).height('');
						var h = $(this).height();
						max = Math.max(max, h);
					}).height(max);
				});

				$("#roletypeform-wrapper").removeClass("hidden");

				var roleId = $(this).attr("data-id");

				// Reset rules
				allowedRules = [];
				allowedRulesMainCompany = [];
				allowedRulesCustomers   = [];
				allowedRulesOwnCompany  = [];
				allowedRulesCompanies   = [];
				allowedRulesDepartments = [];

				$.post("index.php?option=com_redshopb&task=aclroletype.ajaxGetRole",
					{
						"id": roleId,
						"<?php echo Session::getFormToken() ?>": 1
					},
					function(response){
						$("#role_id").val(response.id);
						$("#role_name").val(response.name);

						if (response.hidden == 1) {
							$("#role_hidden_yes").prop("checked", true);
						} else {
							$("#role_hidden_no").prop("checked", true);
						}

						aclAddPermission(response.allowed_rules, "allowed_rules", allowedRules);
						aclAddPermission(response.allowed_rules_main_company, "allowed_rules_main_company", allowedRulesMainCompany);
						aclAddPermission(response.allowed_rules_customers, "allowed_rules_customers", allowedRulesCustomers);
						aclAddPermission(response.allowed_rules_own_company, "allowed_rules_own_company", allowedRulesOwnCompany);
						aclAddPermission(response.allowed_rules_company, "allowed_rules_company", allowedRulesCompanies);
						aclAddPermission(response.allowed_rules_department, "allowed_rules_department", allowedRulesDepartments);
					},
					"JSON"
				)
				.fail(function(response){
					$("#roletypealert").removeClass("alert-info").removeClass("hidden").addClass("alert-error");
					$("#roletypealert p").text(response.responseText);
				});
			});

			// Form cancel button
			$("#form-canel").click(function(event){
				event.preventDefault();

				// Clean up Allowed Permissions
				$(".allowed_list li").remove();

				// Unactive rule tab
				$("#rule_list li.active").removeClass("active");
				$("#rule_list").parent().find(".tab-content > .tab-pane.active").removeClass("active");
				$("#rules_alert").show();

				$("#roletypelist li:not(.active)").fadeIn('fast');
				$("#roletypelist li.active").removeClass("active");

				$("#roletypeform-wrapper").fadeOut(0, function(){
					$("#permission-wrapper").fadeOut(0);
					$("#roletypealert").fadeIn('fast');
				});
			});

			// Form submit button
			$("#form-submit").click(function(event){
				var roleHideSelected = $("#roletypeform-wrapper input[type='radio'][name='role_hide']:checked");
				var roleHideVal = 0;
				if (roleHideSelected.length > 0) {
					roleHideVal = roleHideSelected.val();
				}
				$.post("index.php?option=com_redshopb&task=aclroletype.ajaxSaveRole",
					{
						"id" : $("#role_id").val(),
						"<?php echo Session::getFormToken() ?>" : 1,
						"name" : $("#role_name").val(),
						"hidden" : roleHideVal,
						"allowed_rules" : allowedRules,
						"allowed_rules_main_company" : allowedRulesMainCompany,
						"allowed_rules_customers" : allowedRulesCustomers,
						"allowed_rules_company" : allowedRulesCompanies,
						"allowed_rules_own_company" : allowedRulesOwnCompany,
						"allowed_rules_department" : allowedRulesDepartments
					},
					function(response){
						displayMessage(response, "alert-success");

						// Update tab name of group
						$("#roletypelist li.active a span").text($("#role_name").val());

						// Clean up Allowed Permissions
						$(".allowed_list li").remove();

						// Unactive rule tab
						$("#rule_list li.active").removeClass("active");
						$("#rule_list").parent().find(".tab-content > .tab-pane.active").removeClass("active");
						$("#rules_alert").show();

						$("#roletypelist li:not(.active)").fadeIn('fast');
						$("#roletypelist li.active").removeClass("active");

						$("#roletypeform-wrapper").fadeOut(0, function(){
							$("#permission-wrapper").fadeOut(0);
							$("#roletypealert").fadeIn('fast');
						});
					}
				)
				.fail(function(response){
					displayMessage(response, "alert-error");

					// Unactive rule tab
					$("#rule_list li.active").removeClass("active");
					$("#rule_list").parent().find(".tab-content > .tab-pane.active").removeClass("active");
					$("#rules_alert").show();
				});
			});

			var permissions = Sortable.create(document.getElementById("role_permissions"), {
				group: {
					name: "permissions",
					pull: 'clone',
					put: false
				},
				sort: false,
				ghostClass: "active",
				filter: ".disabled",
				onStart: function(event){
					permissions.option("disabled", true);
				},
				onEnd: function(event) {
					permissions.option("disabled", false);
				},
				onMove: function(event){
					var $item = $(event.dragged);
					var $to   = $(event.to);

					if ($to.attr("id") == "role_permissions"
						|| ($(event.to).attr("id") == "allowed_rules" && allowedRules.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_main_company" && allowedRulesMainCompany.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_customers" && allowedRulesCustomers.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_company" && allowedRulesCompanies.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_own_company" && allowedRulesOwnCompany.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_department" && allowedRulesDepartments.indexOf($item.attr("data-name")) > -1)
					) {
						return false;
					}
				}
			});

			// Company permissions
			var companyPermissions = Sortable.create(document.getElementById("role_permissions_company"), {
				group: {
					name: "company_permissions",
					pull: 'clone',
					put: false
				},
				sort: false,
				ghostClass: "active",
				filter: ".header",
				onStart: function(event){
					companyPermissions.option("disabled", true);
				},
				onEnd: function(event) {
					companyPermissions.option("disabled", false);
				},
				onMove: function(event){
					var $item = $(event.dragged);
					var $to   = $(event.to);

					if ($to.attr("id") == "role_permissions_company"
						|| ($(event.to).attr("id") == "allowed_rules" && allowedRules.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_main_company" && allowedRulesMainCompany.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_customers" && allowedRulesCustomers.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_company" && allowedRulesCompanies.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_own_company" && allowedRulesOwnCompany.indexOf($item.attr("data-name")) > -1)
						|| ($to.attr("id") == "allowed_rules_department" && allowedRulesDepartments.indexOf($item.attr("data-name")) > -1)
					) {
						return false;
					}
				}
			});

			// Allowed Rules
			Sortable.create(document.getElementById("allowed_rules"), {
				group: {
					name: "allowed_rules",
					pull: false,
					put: ['permissions', 'company_permissions']
				},
				sort: false,
				ghostClass: "active",
				filter: ",grid-item",
				onAdd: function(event){
					var $sourceItem = $(event.clone);
					var $item = $(event.item);

					// Add disabled class for source element
					$sourceItem.addClass("disabled");

					allowedRules.push($item.attr("data-name"));

					$item
						.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
						.height(boxHeight);
					incrementActiveTabCounter(1);
				},
				onFilter: function(event){
					if (Sortable.utils.is(event.target, ".icon-remove")) {
						event.item.parentNode.removeChild(event.item);
						var dataName = $(event.item).attr("data-name");
						allowedRules.splice(allowedRules.indexOf(dataName), 1);
						$('.wholePossibleTabs ul li[data-name="'+dataName+'"]').removeClass("disabled");
						incrementActiveTabCounter(-1);
					}
				}
			});

			// Allowed Rules for Main Companies
			Sortable.create(document.getElementById("allowed_rules_main_company"), {
				group: {
					name: "allowed_rules_main_company",
					pull: false,
					put: ['permissions', 'company_permissions']
				},
				sort: false,
				ghostClass: "active",
				filter: ".icon-remove",
				onAdd: function(event){
					var $sourceItem = $(event.clone);
					var $item = $(event.item);

					// Add disabled class for source element
					$sourceItem.addClass("disabled");

					allowedRulesMainCompany.push($item.attr("data-name"));

					$item
						.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
						.height(boxHeight);
					incrementActiveTabCounter(1);
				},
				onFilter: function(event){
					if (Sortable.utils.is(event.target, ".icon-remove")) {
						event.item.parentNode.removeChild(event.item);
						var dataName = $(event.item).attr("data-name");
						allowedRulesMainCompany.splice(allowedRulesMainCompany.indexOf(dataName), 1);
						$('.wholePossibleTabs ul li[data-name="'+dataName+'"]').removeClass("disabled");
						incrementActiveTabCounter(-1);
					}
				}
			});

			// Allowed Rules for Customer Companies
			Sortable.create(document.getElementById("allowed_rules_customers"), {
				group: {
					name: "allowed_rules_customers",
					pull: false,
					put: ['permissions', 'company_permissions']
				},
				sort: false,
				ghostClass: "active",
				filter: ".icon-remove",
				onAdd: function(event){
					var $sourceItem = $(event.clone);
					var $item = $(event.item);

					// Add disabled class for source element
					$sourceItem.addClass("disabled");

					allowedRulesCustomers.push($item.attr("data-name"));

					$item
						.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
						.height(boxHeight);
					incrementActiveTabCounter(1);
				},
				onFilter: function(event){
					if (Sortable.utils.is(event.target, ".icon-remove")) {
						event.item.parentNode.removeChild(event.item);
						var dataName = $(event.item).attr("data-name");
						allowedRulesCustomers.splice(allowedRulesCustomers.indexOf(dataName), 1);
						$('.wholePossibleTabs ul li[data-name="'+dataName+'"]').removeClass("disabled");
						incrementActiveTabCounter(-1);
					}
				}
			});

			// Allowed Rules for Own and Children Companies
			Sortable.create(document.getElementById("allowed_rules_company"), {
				group: {
					name: "allowed_rules_company",
					pull: false,
					put: ['permissions', 'company_permissions']
				},
				sort: false,
				ghostClass: "active",
				filter: ".icon-remove",
				onAdd: function(event){
					var $sourceItem = $(event.clone);
					var $item = $(event.item);

					// Add disabled class for source element
					$sourceItem.addClass("disabled");

					allowedRulesCompanies.push($item.attr("data-name"));

					$item
						.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
						.height(boxHeight);
					incrementActiveTabCounter(1);
				},
				onFilter: function(event){
					if (Sortable.utils.is(event.target, ".icon-remove")) {
						event.item.parentNode.removeChild(event.item);
						var dataName = $(event.item).attr("data-name");
						allowedRulesCompanies.splice(allowedRulesCompanies.indexOf(dataName), 1);
						$('.wholePossibleTabs ul li[data-name="'+dataName+'"]').removeClass("disabled");
						incrementActiveTabCounter(-1);
					}
				}
			});

			// Allowed Rules for Own Companies
			Sortable.create(document.getElementById("allowed_rules_own_company"), {
				group: {
					name: "allowed_rules_own_company",
					pull: false,
					put: ['permissions', 'company_permissions']
				},
				sort: false,
				ghostClass: "active",
				filter: ".icon-remove",
				onAdd: function(event){
					var $sourceItem = $(event.clone);
					var $item = $(event.item);

					// Add disabled class for source element
					$sourceItem.addClass("disabled");

					allowedRulesOwnCompany.push($item.attr("data-name"));

					$item
						.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
						.height(boxHeight);
					incrementActiveTabCounter(1);
				},
				onFilter: function(event){
					if (Sortable.utils.is(event.target, ".icon-remove")) {
						event.item.parentNode.removeChild(event.item);
						var dataName = $(event.item).attr("data-name");
						allowedRulesOwnCompany.splice(allowedRulesOwnCompany.indexOf(dataName), 1);
						$('.wholePossibleTabs ul li[data-name="'+dataName+'"]').removeClass("disabled");
						incrementActiveTabCounter(-1);
					}
				}
			});

			// Allowed Rules for Own and Children Departments
			Sortable.create(document.getElementById("allowed_rules_department"), {
				group: {
					name: "allowed_rules_department",
					pull: false,
					put: ['permissions', 'company_permissions']
				},
				sort: false,
				ghostClass: "active",
				filter: ".icon-remove",
				onAdd: function(event){
					var $sourceItem = $(event.clone);
					var $item = $(event.item);

					// Add disabled class for source element
					$sourceItem.addClass("disabled");

					allowedRulesDepartments.push($item.attr("data-name"));

					$item
						.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
						.height(boxHeight);
					incrementActiveTabCounter(1);
				},
				onFilter: function(event){
					if (Sortable.utils.is(event.target, ".icon-remove")) {
						event.item.parentNode.removeChild(event.item);
						var dataName = $(event.item).attr("data-name");
						allowedRulesDepartments.splice(allowedRulesDepartments.indexOf(dataName), 1);
						$('.wholePossibleTabs ul li[data-name="'+dataName+'"]').removeClass("disabled");
						incrementActiveTabCounter(-1);
					}
				}
			});

			// Trigger on rules tab shown
			$("#rule_list li a[data-toggle='tab']").on("shown", function(event){
				// Hide alert message
				$("#rules_alert").hide();

				var $list = $($(event.target).attr("href") + " ul li");
				var count = $list.length;

				$("#role_permissions li.disabled").removeClass("disabled");
				$("#role_permissions_company li.disabled").removeClass("disabled");

				// Calculate max height if neccessary
				var max = 64;

				$list.each(function(index){
					$(this).height('');
					var h = $(this).height();
					max = Math.max(max, h);

					if ($("#role_permissions li[data-name='" + $(this).attr("data-name") + "']").length > 0) {
						$("#role_permissions li[data-name='" + $(this).attr("data-name") + "']").addClass('disabled');
					}
					else if ($("#role_permissions_company li[data-name='" + $(this).attr("data-name") + "']").length > 0) {
						$("#role_permissions_company li[data-name='" + $(this).attr("data-name") + "']").addClass('disabled');
					}
				});

				$list.each(function(){}).height(max);
				$("#rule_list li.active a span").text(count);

				// Add global height for draggable element can use
				boxHeight = max;
			});

			$('.allowed_list').each(function() {
				var $this = $(this);

				function setHeight() {
					var max = 64;

					$('li', $this).each(function() {
						$(this).height('');
						var h = $(this).height();
						max = Math.max(max, h);
					}).height(max);

					boxHeight = max;
				}

				$(window).on('load resize orientationchange', setHeight);
			});
		});
	})(jQuery);
</script>
<script type="text/javascript">
	function aclAddPermission(results, targetId, dataArray) {
		(function($){
			var $target = $("#" + targetId);
			var count = 0;
			try {
				results.forEach(function(text, index){
					var $item;

					if ($("#role_permissions li[data-name='" + text + "']").length > 0) {
						$item = $("#role_permissions li[data-name='" + text + "']").clone();
					}
					else if ($("#role_permissions_company li[data-name='" + text + "']").length > 0) {
						$item = $("#role_permissions_company li[data-name='" + text + "']").clone();
					}

					if ($item != null) {
						$item.removeClass("disabled")
							.html('<i class="icon-remove pull-right text-error"></i>' + $item.html())
							.appendTo($target);
						dataArray.push($item.attr("data-name"));
						count++;
					}
				});
			}
			catch (e){
				// Do nothing.
			}

			// Update permissions count for each role
			$("#rule_list li a[href='#" + $target.parent().attr("id") + "'] span").text(count);
		})(jQuery);
	}

	function displayMessage(message, elementClass) {
		(function($){
			$("#roletypeform-alert")
				.removeClass("alert-*")
				.addClass(elementClass)
				.html(message)
				.fadeIn('slow', function(){
					$("#roletypeform-alert")
						.delay(3000)
						.fadeOut('fast');
				});
		})(jQuery);
	}
</script>
<div class="aclroletypes">
	<div class="row">
		<div class="col-md-12">
			<ul id="roletypelist" class="nav nav-tabs">
				<?php foreach ($this->items as $item): ?>
				<li><a href="#" data-id="<?php echo $item->id ?>"><span><?php echo $item->name ?></span></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div id="roletypealert" class="alert alert-info">
				<p><?php echo Text::_('COM_REDSHOPB_ACL_NOTICE_CHOOSE_ROLE_EDIT'); ?></p>
			</div>
			<div id="roletypeform-wrapper" class="form form-horizontal" style="display: none;">
				<div class="toolbar">
					<button class="btn btn-success" id="form-submit"><i class="icon icon-save"></i><?php echo Text::_('JTOOLBAR_APPLY') ?></button>
					<button class="btn btn-danger" id="form-canel"><i class="icon icon-remove"></i><?php echo Text::_('JTOOLBAR_CLOSE') ?></button>
				</div>
				<hr />
				<div id="roletypeform-alert" class="alert" style="display: none;">
				</div>
				<div class="form-group">
					<div class="control-label">
						<label><?php echo Text::_('COM_REDSHOPB_ACL_FORM_NAME') ?></label>
					</div>
					<div class="controls">
						<input name="name" type="text" class="input input-xxlarge" id="role_name" value="" />
					</div>
				</div>
				<div class="form-group">
					<div class="control-label">
						<label><?php echo Text::_('COM_REDSHOPB_ACL_FORM_HIDDEN') ?></label>
					</div>
					<div class="controls">
						<label class="radio inline">
							<input type="radio" name="role_hide" id="role_hidden_yes" value="1"><?php echo Text::_('JYES') ?>
						</label>
						<label class="radio inline">
							<input type="radio" name="role_hide" id="role_hidden_no" value="0"><?php echo Text::_('JNO') ?>
						</label>
					</div>
				</div>
				<div class="form-group">
				</div>
				<input type="hidden" id="role_id" name="id" value="" />
			</div>
		</div>
	</div>
	<div id="permission-wrapper" style="display: none;">
		<div class="row">
			<div class="col-md-6">
				<h4><?php echo Text::_('COM_REDSHOPB_ACL_AVAILABLE_PERMISSION') ?></h4>
				<div class="row">
					<div class="col-md-12">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab_per_component" data-toggle="tab">
									<?php echo Text::_('COM_REDSHOPB_ACL_AVAILABLE_PERMISSION_COMPONENT') ?>
								</a>
							</li>
							<li>
								<a href="#tab_per_company" data-toggle="tab">
									<?php echo Text::_('COM_REDSHOPB_ACL_AVAILABLE_PERMISSION_COMPANY') ?>
								</a>
							</li>
						</ul>
						<div class="tab-content wholePossibleTabs">
							<div class="tab-pane active" id="tab_per_component">
								<ul id="role_permissions" class="nav nav-stacked rules_list">
									<?php foreach ($this->permissions as $permission): ?>
									<li data-id="<?php echo $permission->id ?>" data-name="<?php echo $permission->name ?>"
										class="component grid-item">
										<div class="head"><?php echo $permission->group ?></div>
										<div class="text"><?php echo Text::sprintf($permission->title, 'Collection') ?></div>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
							<div class="tab-pane" id="tab_per_company">
								<ul id="role_permissions_company" class="nav nav-stacked rules_list">
									<?php foreach ($this->companyPermissions as $permission): ?>
									<li data-id="<?php echo $permission->id ?>" data-name="<?php echo $permission->name ?>"
										class="company grid-item">
										<div class="head"><?php echo $permission->group ?></div>
										<div class="text"><?php echo Text::sprintf($permission->title, 'Collection') ?></div>
									</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<h4><?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES') ?></h4>
				<div class="row allowedAclRules">
					<div class="col-md-12">
						<ul class="nav nav-pills" id="rule_list">
							<li>
								<a href="#tab_ar" data-toggle="tab">
									<span class="badge badge-info">0</span>
									<?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_GENERAL') ?>
								</a>
							</li>
							<li>
								<a href="#tab_armc" data-toggle="tab">
									<span class="badge badge-info">0</span>
									<?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_MAIN_COMPANY') ?>
								</a>
							</li>
							<li>
								<a href="#tab_arc" data-toggle="tab">
									<span class="badge badge-info">0</span>
									<?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_CUSTOMERS') ?>
								</a>
							</li>
							<li>
								<a href="#tab_arcomp" data-toggle="tab">
									<span class="badge badge-info">0</span>
									<?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_OWN_AND_CHILDREN_COMPANIES') ?>
								</a>
							</li>
							<li>
								<a href="#tab_aroc" data-toggle="tab">
									<span class="badge badge-info">0</span>
									<?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_OWN_COMPANY') ?>
								</a>
							</li>
							<li>
								<a href="#tab_ard" data-toggle="tab">
									<span class="badge badge-info">0</span>
									<?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_OWN_CHILDREN_DEPARTMENTS') ?>
								</a>
							</li>
						</ul>
						<div class="tab-content">
							<div class="message alert alert-warning" id="rules_alert">
								<p><?php echo Text::_('COM_REDSHOPB_ACL_ALLOWED_RULES_NOTICE_SELECT_RULES') ?></p>
							</div>
							<div class="tab-pane" id="tab_ar">
								<ul id="allowed_rules" class="grid rules_list allowed_list"></ul>
							</div>
							<div class="tab-pane" id="tab_armc">
								<ul id="allowed_rules_main_company" class="nav nav-stacked nav-pills nav-tabs rules_list allowed_list"></ul>
							</div>
							<div class="tab-pane" id="tab_arc">
								<ul id="allowed_rules_customers" class="nav nav-stacked nav-pills nav-tabs rules_list allowed_list"></ul>
							</div>
							<div class="tab-pane" id="tab_arcomp">
								<ul id="allowed_rules_company" class="nav nav-stacked nav-pills nav-tabs rules_list allowed_list"></ul>
							</div>
							<div class="tab-pane" id="tab_aroc">
								<ul id="allowed_rules_own_company" class="nav nav-stacked nav-pills nav-tabs rules_list allowed_list"></ul>
							</div>
							<div class="tab-pane" id="tab_ard">
								<ul id="allowed_rules_department" class="nav nav-stacked nav-pills nav-tabs rules_list allowed_list"></ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
