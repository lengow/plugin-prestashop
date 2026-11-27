{*
 * Copyright 2017 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 *  @author	   Team Connector <team-connector@lengow.com>
 *  @copyright 2017 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *}
{* Cancel/refund reason pickers. The script moves them next to the order status form, so no core template is overridden. *}
<div id="lengow-order-reasons"
     data-order-id="{$lengow_reasons_id_order|intval}"
     data-reasons-url="{$lengow_reasons_url|escape:'html':'UTF-8'}"
     data-save-reason-url="{$lengow_save_reason_url|escape:'html':'UTF-8'}"
     data-save-mode-url="{$lengow_save_mode_url|escape:'html':'UTF-8'}">
  <div id="lengow-cancel-reason-block" style="display:none; align-self:center;">
    <div style="position:relative; display:inline-block;">
      <select id="lengow_cancel_reason" class="btn btn-action" style="-webkit-appearance:none; appearance:none; padding-right:2rem; cursor:pointer; text-align:left; text-align-last:left; background-image:url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23363a41' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e&quot;); background-repeat:no-repeat; background-position:right 0.6rem center; background-size:8px 10px;"></select>
      <span id="lengow-cancel-reason-status" style="position:absolute; right:1.4rem; top:50%; transform:translateY(-50%); pointer-events:none; line-height:1;"></span>
    </div>
  </div>
  <div id="lengow-refund-reason-block" style="display:none; align-self:center;">
    <div style="display:inline-flex; align-items:center; gap:0.25rem;">
      <div style="position:relative; display:inline-block;">
        <select id="lengow_refund_reason_action" class="btn btn-action" style="-webkit-appearance:none; appearance:none; padding-right:2rem; cursor:pointer; text-align:left; text-align-last:left; background-image:url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23363a41' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e&quot;); background-repeat:no-repeat; background-position:right 0.6rem center; background-size:8px 10px;"></select>
        <span id="lengow-refund-reason-status" style="position:absolute; right:1.4rem; top:50%; transform:translateY(-50%); pointer-events:none; line-height:1;"></span>
      </div>
      <select id="lengow_refund_mode_action" class="btn btn-action" style="display:none; -webkit-appearance:none; appearance:none; padding-right:2rem; cursor:pointer; text-align:left; text-align-last:left; background-image:url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='4' height='5' viewBox='0 0 4 5'%3e%3cpath fill='%23363a41' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e&quot;); background-repeat:no-repeat; background-position:right 0.6rem center; background-size:8px 10px;"></select>
    </div>
  </div>
</div>
{literal}
<script>
  (function () {
    var wrapper = document.getElementById('lengow-order-reasons');
    if (!wrapper) { return; }
    var cancelBlock = wrapper.querySelector('#lengow-cancel-reason-block');
    var refundBlock = wrapper.querySelector('#lengow-refund-reason-block');

    // An order_actions.html.twig override left by an older version renders its own pickers first: let it run alone.
    if (document.getElementById('lengow-cancel-reason-block') !== cancelBlock) {
      wrapper.parentNode.removeChild(wrapper);
      return;
    }

    // Show the pickers right after the status form of the action bar, where the old override rendered them.
    var statusForm = document.getElementById('update_order_status_action_form');
    if (statusForm && statusForm.parentNode) {
      statusForm.parentNode.insertBefore(refundBlock, statusForm.nextSibling);
      statusForm.parentNode.insertBefore(cancelBlock, statusForm.nextSibling);
    }

    var orderId = parseInt(wrapper.getAttribute('data-order-id'), 10);
    var fetchUrl = wrapper.getAttribute('data-reasons-url');
    var saveReasonUrl = wrapper.getAttribute('data-save-reason-url');
    var saveModeUrl = wrapper.getAttribute('data-save-mode-url');

    var cancelStateId = 0;
    var refundStateId = 0;

    function showStatus(spanId, state) {
      var span = document.getElementById(spanId);
      if (!span) { return; }
      if (state === 'loading') {
        span.innerHTML = '<span class="spinner-border spinner-border-sm text-secondary" role="status" style="width:.85rem;height:.85rem;border-width:.12em;vertical-align:middle;"></span>';
      } else if (state === 'success') {
        span.innerHTML = '<i class="material-icons text-success" style="font-size:1.1rem;vertical-align:middle;">check_circle</i>';
        setTimeout(function () { span.innerHTML = ''; }, 2000);
      } else {
        span.innerHTML = '<i class="material-icons text-danger" style="font-size:1.1rem;vertical-align:middle;">cancel</i>';
        setTimeout(function () { span.innerHTML = ''; }, 3000);
      }
    }

    function saveData(url, data, statusSpanId) {
      showStatus(statusSpanId, 'loading');
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(data)
      })
        .then(function (r) { return r.json(); })
        .then(function (result) {
          showStatus(statusSpanId, result.success ? 'success' : 'error');
        })
        .catch(function () { showStatus(statusSpanId, 'error'); });
    }

    function buildSelect(selectEl, options, selectedValue) {
      selectEl.innerHTML = '';
      var hasEmpty = false;
      Object.keys(options).forEach(function (label) {
        var value = options[label];
        var opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label;
        if (value === '') {
          opt.disabled = true;
          opt.style.color = '#aaa';
          opt.style.fontStyle = 'italic';
          hasEmpty = true;
        }
        if (value === selectedValue) { opt.selected = true; }
        selectEl.appendChild(opt);
      });
      if (hasEmpty && (!selectedValue || selectedValue === '')) {
        selectEl.querySelector('option[value=""]').selected = true;
      }
    }

    function updateVisibility(selectedStateId) {
      cancelBlock.style.display = (cancelStateId > 0 && selectedStateId === cancelStateId) ? 'flex' : 'none';
      refundBlock.style.display = (refundStateId > 0 && selectedStateId === refundStateId) ? 'flex' : 'none';
    }

    function initLengow(data) {
      if (!data.isFromLengow) { return; }

      cancelStateId = data.cancelStateId || 0;
      refundStateId = data.refundStateId || 0;

      var cancelSelect = document.getElementById('lengow_cancel_reason');
      var refundReasonSelect = document.getElementById('lengow_refund_reason_action');
      var refundModeSelect = document.getElementById('lengow_refund_mode_action');

      if (cancelSelect && data.cancelReasons && Object.keys(data.cancelReasons).length > 0) {
        buildSelect(cancelSelect, data.cancelReasons, data.cancelReasonSelected || '');
        cancelSelect.addEventListener('change', function () {
          saveData(saveReasonUrl, { orderId: orderId, reason: this.value }, 'lengow-cancel-reason-status');
        });
      }

      if (refundReasonSelect && data.refundReasons && Object.keys(data.refundReasons).length > 0) {
        buildSelect(refundReasonSelect, data.refundReasons, data.refundReasonSelected || '');
        refundReasonSelect.addEventListener('change', function () {
          saveData(saveReasonUrl, { orderId: orderId, reason: this.value }, 'lengow-refund-reason-status');
        });
      }

      if (refundModeSelect && data.refundModes && Object.keys(data.refundModes).length > 0) {
        buildSelect(refundModeSelect, data.refundModes, data.refundModeSelected || '');
        refundModeSelect.style.display = '';
        refundModeSelect.addEventListener('change', function () {
          var reason = refundReasonSelect ? refundReasonSelect.value : '';
          saveData(saveModeUrl, { orderId: orderId, mode: this.value, reason: reason }, 'lengow-refund-reason-status');
        });
      }

      if (typeof jQuery !== 'undefined') {
        jQuery('#update_order_status_action_input').on('change', function () {
          updateVisibility(parseInt(this.value, 10));
        });
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      fetch(fetchUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(initLengow)
        .catch(function () {});
    });
  })();
</script>
{/literal}
