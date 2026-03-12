{*
 * Copyright 2021 Lengow SAS.
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
 *}
<div class="tab-pane fade" id="lengow-tab-content" role="tabpanel">
    <div class="card">
        <div class="card-body">

            {if $isActiveReturnTrackingNumber || $isActiveReturnCarrier}
            <h4 class="mb-3">Return Shipping</h4>
            <div class="row mb-4">
                {if $isActiveReturnTrackingNumber}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">{$returnTrackingNumberLabel|escape:'html':'UTF-8'}</label>
                        <div class="input-group">
                            <input type="text"
                                   id="lengow_return_tracking_number"
                                   class="form-control"
                                   value="{$returnTrackingNumber|escape:'html':'UTF-8'}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary js-lengow-save"
                                        data-action="save_return_tracking"
                                        data-field="value"
                                        data-source="lengow_return_tracking_number"
                                        data-feedback="lengow_return_tracking_result">
                                    Save
                                </button>
                            </div>
                        </div>
                        <div id="lengow_return_tracking_result" class="mt-1"></div>
                    </div>
                </div>
                {/if}

                {if $isActiveReturnCarrier}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">{$returnCarrierLabel|escape:'html':'UTF-8'}</label>
                        <div class="input-group">
                            <select id="lengow_return_carrier" class="form-control">
                                <option value="">—</option>
                                {foreach from=$carriers key=label item=carrierId}
                                    <option value="{$carrierId|intval}"
                                        {if $carrierId == $returnCarrier}selected{/if}>
                                        {$label|escape:'html':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary js-lengow-save"
                                        data-action="save_return_carrier"
                                        data-field="value"
                                        data-source="lengow_return_carrier"
                                        data-feedback="lengow_return_carrier_result">
                                    Save
                                </button>
                            </div>
                        </div>
                        <div id="lengow_return_carrier_result" class="mt-1"></div>
                    </div>
                </div>
                {/if}
            </div>
            {/if}

            {if $refundReasons|@count > 0 || $refundModes|@count > 0}
            <h4 class="mb-3">Refund</h4>
            <div class="row">
                {if $refundReasons|@count > 0}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Refund reason</label>
                        <select id="lengow_refund_reason" class="form-control js-lengow-autoselect"
                                data-action="save_refund_reason"
                                data-field="value">
                            {foreach from=$refundReasons key=label item=value}
                                <option value="{$value|escape:'html':'UTF-8'}"
                                    {if $value == $refundReasonSelected}selected{/if}>
                                    {$label|escape:'html':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                {/if}

                {if $refundModes|@count > 0}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-control-label">Refund mode</label>
                        <select id="lengow_refund_mode" class="form-control js-lengow-autoselect"
                                data-action="save_refund_mode"
                                data-field="value">
                            {foreach from=$refundModes key=label item=value}
                                <option value="{$value|escape:'html':'UTF-8'}"
                                    {if $value == $refundModeSelected}selected{/if}>
                                    {$label|escape:'html':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                {/if}
            </div>
            {/if}

        </div>
    </div>
</div>

<script>
var lgBaseUrl = "{$ajax_url|escape:'javascript':'UTF-8'}";
var lgOrderId = {$orderId|intval};
{literal}
(function () {
    function lgSave(action, field, value, feedbackId) {
        var sep = lgBaseUrl.indexOf('?') === -1 ? '?' : '&';
        var url = lgBaseUrl + sep + 'action=' + action + '&id_order=' + lgOrderId + '&' + field + '=' + encodeURIComponent(value);
        fetch(url, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
        })
            .then(function (r) { return r.json(); })
            .then(function (json) {
                if (!feedbackId) { return; }
                var el = document.getElementById(feedbackId);
                if (!el) { return; }
                el.innerHTML = json.success
                    ? '<span class="text-success">&#10003;</span>'
                    : '<span class="text-danger">&#10007; ' + (json.message || '') + '</span>';
                setTimeout(function () { el.innerHTML = ''; }, 4000);
            })
            .catch(function () {
                if (!feedbackId) { return; }
                var el = document.getElementById(feedbackId);
                if (el) { el.innerHTML = '<span class="text-danger">Error</span>'; }
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Move the Lengow tab link and content into the native PS tab bar (État/Documents/Transporteurs row).
        // PS renders hook tabs in a separate #order_hook_tabs sibling div; we merge them in via DOM move.
        var hookContainer = document.getElementById('order_hook_tabs');
        if (hookContainer) {
            var prevSibling = hookContainer.previousElementSibling;
            if (prevSibling) {
                var nativeTabList = prevSibling.querySelector('ul.nav-tabs');
                var nativeTabContent = prevSibling.querySelector('div.tab-content');
                var hookTabList = hookContainer.querySelector('ul.nav-tabs');
                var hookTabContent = hookContainer.querySelector('div.tab-content');
                if (nativeTabList && hookTabList) {
                    while (hookTabList.firstElementChild) {
                        nativeTabList.appendChild(hookTabList.firstElementChild);
                    }
                }
                if (nativeTabContent && hookTabContent) {
                    while (hookTabContent.firstElementChild) {
                        nativeTabContent.appendChild(hookTabContent.firstElementChild);
                    }
                }
                hookContainer.remove();
            }
        }

        document.querySelectorAll('.js-lengow-save').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = document.getElementById(this.dataset.source);
                lgSave(this.dataset.action, this.dataset.field, src ? src.value : '', this.dataset.feedback || null);
            });
        });

        document.querySelectorAll('.js-lengow-autoselect').forEach(function (sel) {
            sel.addEventListener('change', function () {
                lgSave(this.dataset.action, this.dataset.field, this.value, null);
            });
        });
    });
}());
{/literal}
</script>
