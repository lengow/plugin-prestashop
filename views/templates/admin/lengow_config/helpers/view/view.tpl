<script type="text/javascript" src="/modules/lengow/views/js/admin.js"></script>
<!DOCTYPE html>
<html lang="fr">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
{if isset($display_error)}
    {if $display_error}
        <div class="error">{l s='An error occured during the form validation' mod='lengow'}</div>
    {else}
        <div class="conf">{l s='Configuration updated' mod='lengow'}</div>
    {/if}
{/if}
<form id="_form" class="form-horizontal" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" >
    <div class="form-group">
        <fieldset id="fieldset_2">
            <legend>{l s='Security' mod='lengow'}</legend>
            <label for="lengow_authorized_ip" class="col-sm-2 control-label">{l s='Authorised IP' mod='lengow'}</label>
            <div class="col-sm-10">
                <input type="text" name="lengow_authorized_ip" id="lengow_authorized_ip" value="{$lengow_authorized_ip|escape:'htmlall':'UTF-8'}" class="form-control" size="100" />
            </div>
            <div class="clear"></div>
            <div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
            <br />
            <div class="margin-form">
                <input type="submit" id="_form_submit_btn" value="{l s='Save' mod='lengow'}" name="submitlengow" class="btn btn-default" />
            </div>
        </fieldset>
    </div>
        <br />
    <div class="form-group">
        <fieldset id="fieldset_3">
            <legend>{l s='Tracking' mod='lengow'}</legend>
            <label for="lengow_tracking" class="col-sm-2 control-label">{l s='Tracker type choice' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_tracking" class="form-control" id="lengow_tracking">
                    {foreach from=$options.trackers item=option}
                        <option value="{$option->id|escape:'htmlall':'UTF-8'}"{if $option->id == $lengow_tracking} selected="selected"{/if}>{$option->name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
            <br />
            <div class="margin-form">
                <input type="submit" id="_form_submit_btn" value="{l s='Save' mod='lengow'}" name="submitlengow" class="btn btn-default" />
            </div>
        </fieldset>
        <br />
        <fieldset id="fieldset_4">
            <legend>{l s='Export parameters' mod='lengow'}</legend>
            <label>{l s='Default export carrier' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_carrier_default" class="" id="lengow_carrier_default">
                    {foreach from=$options.carriers item=option}
                        <option value="{$option.id_carrier|escape:'htmlall':'UTF-8'}"{if $option.id_carrier == $lengow_carrier_default} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <p class="preference_description">{l s='The shipping costs will be calculated based on the selected carrier' mod='lengow'}</p>
            </div>
            <label>{l s='Export only selection' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_selection" id="active_on" value="1" {if $lengow_export_selection == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_selection" id="active_off" value="0" {if $lengow_export_selection == 0} checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If don\'t want to export all your available products, click "yes" and go onto Tab Prestashop to select your products' mod='lengow'}</p>
            </div>
            <label>{l s='Export disabled products' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_disabled" id="active_on" value="1" {if $lengow_export_disabled == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_disabled" id="active_off" value="0" {if $lengow_export_disabled == 0} checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If you want to export disabled products, click "yes".' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Auto export of new product(s)' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_new" id="active_on" value="1" {if $lengow_export_new == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_new" id="active_off" value="0" {if $lengow_export_new == 0} checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If you click "yes" your new product(s) will be automatically exported on the next feed' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Export product variations' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_all_variations" id="active_on" value="1" {if $lengow_export_all_variations == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_all_variations" id="active_off" value="0" {if $lengow_export_all_variations == 0} checked="checked"{/if}/>
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If don\'t want to export all your products\' variations, click "no"' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Export product features' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_features" id="active_on" value="1" {if $lengow_export_features == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_features" id="active_off" value="0" {if $lengow_export_features == 0} checked="checked"{/if}/>
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If you click "yes", your product(s) will be exported with features.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Title + attributes + features' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_fullname" id="active_on" value="1" {if $lengow_export_fullname == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_fullname" id="active_off" value="0" {if $lengow_export_fullname == 0} checked="checked"{/if}/>
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Select this option if you want a variation product title as title + attributes + feature. By default the title will be the product name' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Export out of stock product' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_out_stock" id="active_on" value="1" {if $lengow_export_out_stock == 1} checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_out_stock" id="active_off" value="0" {if $lengow_export_out_stock == 0} checked="checked"{/if}/>
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Select this option if you want to export out of stock products.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Type of images to export' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_image_type" class="" id="lengow_image_type">
                    {foreach from=$options.images item=option}
                        <option value="{$option.id_image_type|escape:'htmlall':'UTF-8'}"{if $option.id_image_type == $lengow_image_type} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Number images to export' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_images_count" class="" id="lengow_images_count">
                    {foreach from=$options.images_count item=option}
                        <option value="{$option->id|escape:'htmlall':'UTF-8'}"{if $option->id == $lengow_images_count} selected="selected"{/if}>{$option->name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Export default format' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_export_format" class="" id="lengow_export_format">
                    {foreach from=$options.formats item=option}
                        <option value="{$option->id|escape:'htmlall':'UTF-8'}"{if $option->id == $lengow_export_format} selected="selected"{/if}>{$option->name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Export in a file' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_export_file"id="active_on" value="1" {if $lengow_export_file}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_export_file"id="active_off" value="0" {if $lengow_export_file == 0}checked="checked"{/if}  />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='You should use this option if you have 3,000 products or more' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Fields to export' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_export_fields[]" class="lengow-select" size="25" multiple="multiple">
                    {foreach from=$options.export_fields item=field}
                        <option value="{$field->id|escape:'htmlall':'UTF-8'}"{if $field->id|in_array:$lengow_export_fields} selected="selected"{/if}>{$field->name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <p class="preference_description">{l s='Maintain "control key or command key" to select fields.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Product features to export' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_export_select_features[]" class="lengow-select" size="10" multiple="multiple">
                    {foreach from=$options.export_features item=feature}
                        <option value="{$feature->id|escape:'htmlall':'UTF-8'}"{if $feature->id|in_array:$lengow_export_select_features} selected="selected"{/if}>{$feature->name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <p class="preference_description">{l s='Maintain "control key or command key" to select features.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Your export script' mod='lengow'}</label>
            <div class="margin-form">
                {$url_feed_export|escape:'quotes':'UTF-8'}
            </div>
            <div class="clear"></div>
            <label>{l s='Your export file(s) available' mod='lengow'}</label>
            <div class="margin-form">
                {$lengow_export_feed_files|escape:'quotes':'UTF-8'}
            </div>
            <div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
        </fieldset>
        <br />
        {if $lengow_feed_management}
            <fieldset id="fieldset_5"> <legend>{l s='Feeds' mod='lengow'}</legend>
                {$lengow_flow|escape:'htmlall':'UTF-8'}
                <p class="preference_description">{l s='If you use the backoffice of the Lengow module, migrate your feed when you are sure to be ready' mod='lengow'}<br />
                    {l s='If you want to use the file export, don\'t use this fonctionality. Please contact Lengow Support Team' mod='lengow'}
                </p>
                <div class="clear"></div>
            </fieldset>
            <br />
        {/if}
        <fieldset id="fieldset_6"> <legend>{l s='Import parameters' mod='lengow'}</legend>
            <label>{l s='Status of process orders' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_order_process" class="" id="lengow_order_process">
                    {foreach from=$options.states item=option}
                        <option value="{$option.id_order_state|escape:'htmlall':'UTF-8'}"{if $option.id_order_state == $lengow_order_process} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Status of shipped orders' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_order_shipped" class="" id="lengow_order_shipped">
                    {foreach from=$options.states item=option}
                        <option value="{$option.id_order_state|escape:'htmlall':'UTF-8'}"{if $option.id_order_state == $lengow_order_shipped} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Status of cancelled orders' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_order_cancel" class="" id="lengow_order_cancel">
                    {foreach from=$options.states item=option}
                        <option value="{$option.id_order_state|escape:'htmlall':'UTF-8'}"{if $option.id_order_state == $lengow_order_cancel} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Status of orders shipped by marketplaces' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_order_shippedByMp" class="" id="lengow_order_shippedByMp">
                    {foreach from=$options.states item=option}
                        <option value="{$option.id_order_state|escape:'htmlall':'UTF-8'}"{if $option.id_order_state == $lengow_order_shippedByMp} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Associated payment method' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_method_name" class="" id="lengow_method_name">
                    {foreach from=$options.shippings item=option}
                        <option value="{$option->id|escape:'htmlall':'UTF-8'}"{if $option->id == $lengow_method_name} selected="selected"{/if}>{$option->name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="clear"></div>
            <label>{l s='Default carrier' mod='lengow'}</label>
            <div class="margin-form">
                <select name="lengow_import_carrier_default" class="" id="lengow_import_carrier_default">
                    {foreach from=$options.carriers item=option}
                        <option value="{$option.id_carrier|escape:'htmlall':'UTF-8'}"{if $option.id_carrier == $lengow_carrier_default} selected="selected"{/if}>{$option.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <p class="preference_description">{l s='Your default carrier' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Import from x days' mod='lengow'}</label>
            <div class="margin-form">
                <input type="text" name="lengow_import_days" id="lengow_import_days" value="{$lengow_import_days|escape:'htmlall':'UTF-8'}" class="" size="20" /> <sup>*</sup></div>
            <div class="clear"></div>
            <!--<label>{l s='Forced price' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_force_price"id="active_on" value="1" {if $lengow_force_price}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_force_price"id="active_off" value="0" {if $lengow_force_price == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='This option allows to force the product prices of the marketplace orders during the import' mod='lengow'}</p>
            </div>
            <div class="clear"></div>-->
            <label>{l s='Force Products' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_import_force_product"id="active_on" value="1" {if $lengow_import_force_product}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_import_force_product"id="active_off" value="0" {if $lengow_import_force_product == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Yes if you want to force import of disabled or out of stock product' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Import processing fee' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_import_processing_fee"id="active_on" value="1" {if $lengow_import_processing_fee}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_import_processing_fee"id="active_off" value="0" {if $lengow_import_processing_fee == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Yes if you want have marketplace processing fee inside order' mod='lengow'}</p>
            </div>
            <label>{l s='Fictitious emails' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_import_fake_email"id="active_on" value="1" {if $lengow_import_fake_email}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_import_fake_email"id="active_off" value="0" {if $lengow_import_fake_email == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Yes if you want to import orders with fictitious email' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <div class="clear"></div>
            <label>{l s='Markeplace shipping method' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_mp_shipping_method"id="active_on" value="1" {if $lengow_mp_shipping_method}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_mp_shipping_method"id="active_off" value="0" {if $lengow_mp_shipping_method == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Yes if you want your orders to use marketplace shipping method' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Import orders shipped by marketplaces' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_import_shipped_by_mp" id="active_on" value="1" {if $lengow_import_shipped_by_mp}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_import_shipped_by_mp" id="active_off" value="0" {if $lengow_import_shipped_by_mp == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If the order is shipped by the marketplace, product stock will NOT be decremented.' mod='lengow'}</p>
            </div>
            <label>{l s='Report email' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_report_mail" id="active_on" value="1" {if $lengow_report_mail}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_report_mail" id="active_off" value="0" {if $lengow_report_mail == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='If enabled, you will receive a report with every import on the email address configured.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <label>{l s='Send reports to' mod='lengow'}</label>
            <div class="margin-form">
                <input type="text" name="lengow_email_address" id="lengow_email_address" value="{$lengow_email_address|escape:'htmlall':'UTF-8'}" class="" size="50" />
                <p class="preference_description">{l s='If report emails are activated, the reports will be send to the specified address. Otherwise it will be your default shop email address.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <div class="clear"></div>
            <div class="clear"></div>
            <label>{l s='Limit to one order per import process' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_import_single" id="active_on" value="1" {if $lengow_import_single}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_import_single" id="active_off" value="0" {if $lengow_import_single == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
                <p class="preference_description">{l s='Useful for prestashop versions from 1.5.2 to 1.5.4.* : avoids importing orders twice.' mod='lengow'}</p>
            </div>
            <div class="clear"></div>
            <div class="clear"></div>
            <label>{l s='Import state' mod='lengow'}</label>
            <div class="margin-form">
                {$lengow_is_import|escape:'quotes':'UTF-8'}
            </div>
            <div class="clear"></div>
            <label>{l s='Your import script' mod='lengow'}</label>
            <div class="margin-form">
                {$url_feed_import|escape:'quotes':'UTF-8'}
            </div>
            <div class="clear"></div>
            <div class="small"><sup>*</sup> {l s='Required field' mod='lengow'}</div>
        </fieldset>
        <br />
        <fieldset id="fieldset_7"><legend>{l s='Cron' mod='lengow'}</legend>
            {$lengow_cron|escape:'quotes':'UTF-8'}
            <div class="clear"></div>
        </fieldset>
        <br />
        <fieldset id="fieldset_7"> <legend>{l s='Developer' mod='lengow'}</legend>
            <label>{l s='Debug mode' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_debug"id="active_on" value="1" {if $lengow_debug}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_debug"id="active_off" value="0" {if $lengow_debug == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
            </div>
            <div class=:"clear"></div>
            <label>{l s='Export timeout' mod='lengow'}</label>
            <div class="margin-form">
                <input type="text" name="lengow_export_timeout" id="lengow_export_timeout" value="{$lengow_export_timeout|escape:'htmlall':'UTF-8'}" class="" size="20" /> <sup>*</sup></div>
            <div class="clear"></div>
            <!--<label>{l s='Feed management' mod='lengow'}</label>
            <div class="margin-form">
                <input type="radio" name="lengow_feed_management"id="active_on" value="1" {if $lengow_feed_management}checked="checked"{/if} />
                <label class="t" for="active_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Enable' mod='lengow'}" title="{l s='Enable' mod='lengow'}" />
                </label>
                <input type="radio" name="lengow_feed_management"id="active_off" value="0" {if $lengow_feed_management == 0}checked="checked"{/if} />
                <label class="t" for="active_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='Disable' mod='lengow'}" title="{l s='Disable' mod='lengow'}" />
                </label>
            </div>
            <div class=:"clear"></div>-->
            <label>{l s='Logs' mod='lengow'}</label>
            <div class="margin-form">
                {$log_files|escape:'quotes':'UTF-8'}
            </div>
            <div class="margin-form">
                <input type="submit" id="_form_submit_btn" value="{l s='Save' mod='lengow'}" name="submitlengow" class="button" />
            </div>
            <div class="clear"></div>
            <div class="small"><sup>*</sup>{l s='Required field' mod='lengow'}</div>
        </fieldset>
    </form>
</html>