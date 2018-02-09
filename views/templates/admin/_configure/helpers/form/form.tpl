{**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'linkbutton'}
		<a class="btn btn-default" id="{$input.id}" href="#">
			<i class="icon-check"></i>
			{l s=$input.buttonText mod='wirecardpaymentgateway'}
		</a>
		<script type="text/javascript">
			$(function () {
				$('#{$input.id}').on('click', function() {
					$.ajax({
						type: 'POST',
						{** this url doesn't work when escaped *}
						url: '{$ajax_configtest_url}',
						dataType: 'json',
						data: {
							action: 'TestConfig',
							method: '{$input.method}',
							ajax: true
						},
						success: function (jsonData) {
							if (jsonData) {
								$.fancybox({
									fitToView: true,
									content: '<div><fieldset><legend>{l s='Test result' mod='wirecardceecheckoutseamless'}</legend>' +
									'<label>{l s='Status' mod='wirecardpaymentgateway'}:</label>' +
									'<div class="margin-form" style="text-align:left;">' + jsonData.status + '</div><br />' +
									'<label>{l s='Message' mod='wirecardpaymentgateway'}:</label>' +
									'<div class="margin-form" style="text-align:left;">' + jsonData.message + '</div></fieldset></div>'
								});
							}
						}
					});
				});
			 });
		</script>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}
