<?php
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

/**
 * Lengow Form class
 *
 */
class LengowConfigurationForm
{

    public $fields;

    protected $locale;

    public function __construct($params)
    {
        $this->fields = isset($params['fields']) ? $params['fields'] : false;
        $this->locale = new LengowTranslation();
    }

    public function build()
    {

    }

    public function buildShopInputs($shopId, $displayKey)
    {
        $html = '';
        foreach ($displayKey as $key) {
            if (isset($this->fields[$key])) {
                if (isset($this->fields[$key]['shop']) && $this->fields[$key]['shop']) {
                    $html.= $this->input($key, $this->fields[$key], $shopId);
                }
            }
        }
        return $html;
    }

    public function buildInputs($displayKey)
    {
        $html = '';
        foreach ($displayKey as $key) {
            if (isset($this->fields[$key])) {
                if (!isset($this->fields[$key]['shop']) || !$this->fields[$key]['shop']) {
                    $html .= $this->input($key, $this->fields[$key], null);
                }
            }
        }
        return $html;
    }

    public function input($key, $input, $shopId = null)
    {
        $html = '';
        $name = $shopId ?  $key.'['.$shopId.']' : $key;
        if ($shopId) {
            $value = LengowConfiguration::get($key, null, null, $shopId);
        } else {
            $value = LengowConfiguration::getGlobalValue($key);
        }
        $readonly = isset($input['readonly']) && $input['readonly'] ? 'readonly' : '';
        $inputType = isset($input['type']) ? $input['type'] : 'text';
        $legend = isset($input['legend']) ? $input['legend'] : '';
        $label = isset($input['label']) ? $input['label'] : '';
        $placeholder = isset($input['placeholder']) ? $input['placeholder'] : '';
        $html.= '<div class="form-group '.Tools::strtolower($name).'">';
        switch ($inputType) {
            case 'checkbox':
                $checked = $value ? 'checked' : '';
                $html.='<div class="lgw-switch '.$checked.'"><label><div><span></span>';
                $html.='<input name="'.$name.'" type="checkbox" '.$checked.' '.$readonly.' >';
                $html.='</div>'. $label;
                $html.='</label></div></div>';
                $html .= '<span class="legend">' . $legend . '</span>';
                break;
            case 'text':
                $html.= '<label class="control-label">'.$label.'</label>
                    <input type="text" name="'.$name.'"
                        class="form-control" placeholder="'.$placeholder.'"
                        value="'.$value.'" '.$readonly.'>
                    </div><span class="legend">'.$legend.'</span>';
                break;
            case 'select':
                $html.= '<label class="control-label">'.$label.'</label>

                    <select class="form-control lengow_select" name="'.$name.'">';
                foreach ($input['collection'] as $row) {
                    $selected =  $row['id'] == $value ? 'selected' : '';
                    $html.='<option value="'.$row['id'].'" '.$selected.'>'.$row['text'].'</option>';
                }
                $html.= '</select><span class="legend">'.$legend.'</span></div>';
                break;
            case 'day':
                $html.= '<label class="control-label">'.$label.'</label>
                        <div class="input-group">
                            <input type="number" name="'.$name.'" class="form-control" value="'.$value.'" '
                            .$readonly.' min="1" max="99">
                            <div class="input-group-addon">
                                <div class="unit">'.$this->locale->t('order_setting.screen.nb_days').'</div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <span class="legend">'.$legend.'</span>
                    </div>';
                break;
        }
        return $html;
    }

    public function postProcess($checkboxKeys)
    {
        if (_PS_VERSION_ < '1.5') {
            $shopCollection = array(array('id_shop' => 1));
        } else {
            $sql = 'SELECT id_shop FROM '._DB_PREFIX_.'shop WHERE active = 1';
            $shopCollection = Db::getInstance()->ExecuteS($sql);
        }

        foreach ($_REQUEST as $key => $value) {
            if (isset($this->fields[$key])) {
                if (isset($this->fields[$key]['shop']) && $this->fields[$key]['shop']) {
                    foreach ($value as $shopId => $shopValue) {
                        if (isset($this->fields[$key]['type']) &&
                            $this->fields[$key]['type'] == 'checkbox' && $shopValue == 'on') {
                            $shopValue = true;
                        }
                        $this->checkAndLog($key, $shopValue, $shopId);
                        LengowConfiguration::updateValue($key, $shopValue, false, null, $shopId);
                    }
                } else {
                    if (is_array($value)) {
                        $this->checkAndLog($key, join(',', $value));
                        LengowConfiguration::updateGlobalValue($key, join(',', $value));
                    } else {
                        if (isset($this->fields[$key]['type']) &&
                            $this->fields[$key]['type'] == 'checkbox' && $value == 'on') {
                            $value = true;
                        }
                        $this->checkAndLog($key, $value);
                        LengowConfiguration::updateGlobalValue($key, $value);
                    }
                }
            }
        }
        foreach ($shopCollection as $shop) {
            $shopId = $shop['id_shop'];
            foreach ($this->fields as $key => $value) {
                if (!in_array($key, $checkboxKeys)) {
                    continue;
                }
                if ($value['type'] == 'checkbox' && isset($value['shop']) && $value['shop']) {
                    if (!isset($_REQUEST[$key][$shopId])) {
                        $this->checkAndLog($key, false, $shopId);
                        LengowConfiguration::updateValue($key, false, false, null, $shopId);
                    }
                }
            }
        }
        foreach ($this->fields as $key => $value) {
            if (!in_array($key, $checkboxKeys)) {
                continue;
            }
            if ((!isset($value['shop']) || !$value['shop'])) {
                if (!isset($_REQUEST[$key])) {
                    $this->checkAndLog($key, false, $shopId);
                    LengowConfiguration::updateGlobalValue($key, false);
                }
            }
        }
    }

    public function checkAndLog($key, $value, $shopId = null)
    {
        if (is_null($shopId)) {
            $old_value = LengowConfiguration::getGlobalValue($key);
        } else {
            $old_value = LengowConfiguration::get($key, null, null, $shopId);
        }
        if (isset($this->fields[$key]['type']) && $this->fields[$key]['type'] == 'checkbox') {
            $value = (int)$value;
            $old_value = (int)$old_value;
        } elseif ($key == 'LENGOW_ACCESS_TOKEN' || $key == 'LENGOW_SECRET_TOKEN') {
            $value = preg_replace("/[a-zA-Z0-9]/", '*', $value);
            $old_value = preg_replace("/[a-zA-Z0-9]/", '*', $old_value);
        }
        if ($old_value != $value && !is_null($shopId)) {
            LengowMain::log(
                'Setting',
                LengowMain::setLogMessage('log.setting.setting_change_for_shop', array(
                    'key'       => $key,
                    'old_value' => $old_value,
                    'value'     => $value,
                    'shop_id'   => $shopId
                ))
            );
        } elseif ($old_value != $value) {
            LengowMain::log(
                'Setting',
                LengowMain::setLogMessage('log.setting.setting_change', array(
                    'key'       => $key,
                    'old_value' => $old_value,
                    'value'     => $value
                ))
            );
        }
    }
}
