/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

;jQuery_1_11_3(document).ready(function() {

	$(document.body).addClass('lengow_body');

	// Update one flow’
	$('.lengow-migrate-action').each(function(i) {
		$(this).click(function() {
			var id_flow = $(this).data('flow'),
				params = '&idFlow=' + id_flow + 
						 '&format=' + $('#format-' + id_flow).val() + 
						 '&mode=' + $('#mode-' + id_flow).val() + 
						 '&all=' + $('#all-' + id_flow).val() + 
						 '&cur=' + $('#currency-' + id_flow + ' option:selected').attr('id') + 
						 '&shop=' + $('#shop-' + id_flow + ' option:selected').attr('id') + 
						 '&lang=' + $('#lang-' + id_flow + ' option:selected').attr('id');console.log($(this).data('url') + params);
			$.getJSON($(this).data('url') + params, function(json) {
					if (json.return) {
						$('#lengow-flux-' + id_flow).html(json.flow);
					}
			  	});
			return false;
		});
	});
	// Update all flow
	$('.lengow-migrate-action-all').each(function(i) {
		$(this).click(function() {
			if (confirm('Are you sure ? You will update all feeds with this settings, continue ?')) {
				var id_flow = $(this).data('flow'),
				params = '&format=' + $('#format-' + id_flow).val() + 
						 '&mode=' + $('#mode-' + id_flow).val() + 
						 '&all=' + $('#all-' + id_flow).val() + 
						 '&cur=' + $('#currency-' + id_flow + ' option:selected').attr('id') + 
						 '&shop=' + $('#shop-' + id_flow + ' option:selected').attr('id') + 
						 '&lang=' + $('#lang-' + id_flow + ' option:selected').attr('id');
				$.getJSON($(this).data('url') + params, function(json) {
					if (json.return) {
						$('.lengow-flux').each(function(i) {
							$(this).html(json.flow);
						});
					}
			  	});
			} else {
				return false;
			}
			return false;
		});
	});
	// Reimport Order
	$('#reimport-order').click(function(e){
		var url = $(this).data('url');
		var orderid = $(this).data('orderid');
		var lengoworderid = $(this).data('lengoworderid');
		var feed_id = $(this).data('feedid');
		var version = $(this).data('version');

		var datas = {};
		datas['url'] = url;
		datas['orderid'] = orderid;
		datas['lengoworderid'] = lengoworderid;
		datas['feed_id'] = feed_id;
		if (version < '1.5')
			datas['action'] = 'reimport_order';

		// Show loading div
		$('#ajax_running').fadeIn(300);
		$.getJSON(url, datas, function(data) {
			$('#ajax_running').fadeOut(0);
			if (data.status == 'success') {
				window.location.replace(data.new_order_url);
			} else {
				alert(data.msg);
			}
			
		});
		return false;
	});
	$(".switchLengow").bootstrapSwitch();
});

