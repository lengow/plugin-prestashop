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

    public function __construct($params)
    {
        $this->fields = isset($params['fields']) ? $params['fields'] : false;
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
        $html.= '<div class="form-group '.Tools::strtolower($name).'">';
        switch ($inputType) {
            case 'checkbox':
                $html.='<div class="col-sm-offset-2 col-sm-10"><div class="checkbox"><label>';
                $checked = $value ? 'checked' : '';
                $html.= '<input name="'.$name.'" type="checkbox" '.$checked.' '.$readonly.' class="lengow_switch">';
                $html.= '<span class="lengow_label_text">'.$input['label'].'</span>';
                $html.= '</label><span class="legend">'.$legend.'</span></div></div></div>';
                break;
            case 'text':
                $html.= '<label class="col-sm-2 control-label">'.$input['label'].'</label>
                    <div class="col-sm-10">
                        <input type="text" name="'.$name.'"
                            class="form-control" placeholder="'.$input['label'].'"
                            value="'.$value.'" '.$readonly.'>
                    </div>
                    <span class="legend">'.$legend.'</span>
                    </div>';
                break;
            case 'select':
                $html.= '<label class="col-sm-2 control-label">'.$input['label'].'</label>
                    <div class="col-sm-10">
                    <select class="form-control lengow_select" name="'.$name.'">';
                foreach ($input['collection'] as $row) {
                    $selected =  $row['id'] == $value ? 'selected' : '';
                    $html.='<option value="'.$row['id'].'" '.$selected.'>'.$row['text'].'</option>';
                }
                $html.= '</select></div><span class="legend">'.$legend.'</span></div>';
                break;
            case 'tag':
                $html.= '<label class="col-sm-2 control-label">'.$input['label'].'</label>
                    <div class="col-sm-10">
                    <select class="form-control lengow_select" name="'.$name.'[]" multiple="multiple">';
                $collection = explode(',', $value);
                foreach ($collection as $row) {
                    if (Tools::strlen($row) > 0) {
                        $html.='<option value="'.$row.'" selected>'.$row.'</option>';
                    }
                }
                $html.= '</select></div><span class="legend">'.$legend.'</span></div>';
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
                        LengowConfiguration::updateValue($key, $shopValue, false, null, $shopId);
                    }
                } else {
                    if (is_array($value)) {
                        LengowConfiguration::updateGlobalValue($key, join(',', $value));
                    } else {
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
                    LengowConfiguration::updateGlobalValue($key, false);
                }
            }
        }
    }
}
