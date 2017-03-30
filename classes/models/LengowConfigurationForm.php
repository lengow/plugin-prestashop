<?php
/**
 * Copyright 2017 Lengow SAS.
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
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Configuration Form Class
 */
class LengowConfigurationForm
{
    /**
     * @var array $fields checkbox keys
     */
    public $fields;

    /**
     * @var LengowTranslation $locale Lengow translation instance
     */
    protected $locale;

    /**
    * Construct
    */
    public function __construct($params)
    {
        $this->fields = isset($params['fields']) ? $params['fields'] : false;
        $this->locale = new LengowTranslation();
    }

    /**
    * Construct Lengow setting input for shop
    *
    * @param integer $idShop      Prestashop shop id
    * @param array   $displayKeys names of Lengow setting
    *
    * @return string
    */
    public function buildShopInputs($idShop, $displayKeys)
    {
        $html = '';
        foreach ($displayKeys as $key) {
            if (isset($this->fields[$key])) {
                if (isset($this->fields[$key]['shop']) && $this->fields[$key]['shop']) {
                    $html.= $this->input($key, $this->fields[$key], $idShop);
                }
            }
        }
        return $html;
    }

    /**
    * Construct Lengow setting input
    *
    * @param array $displayKeys names of Lengow setting
    *
    * @return string
    */
    public function buildInputs($displayKeys)
    {
        $html = '';
        foreach ($displayKeys as $key) {
            if (isset($this->fields[$key])) {
                if (!isset($this->fields[$key]['shop']) || !$this->fields[$key]['shop']) {
                    $html .= $this->input($key, $this->fields[$key], null);
                }
            }
        }
        return $html;
    }

    /**
    * Get lengow input
    *
    * @param string  $key    name of Lengow setting
    * @param array   $input  all Lengow settings
    * @param integer $idShop Prestashop shop id
    *
    * @return string
    */
    public function input($key, $input, $idShop = null)
    {
        $html = '';
        $name = $idShop ?  $key.'['.$idShop.']' : $key;
        if ($idShop) {
            $value = LengowConfiguration::get($key, null, null, $idShop);
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
                $html.='</div>'.$label;
                $html.='</label></div></div>';
                if (!empty($legend)) {
                    $html.= '<span class="legend" style="display:block;">'.$legend.'</span>';
                }
                break;
            case 'text':
                $html.= '<label class="control-label">'.$label.'</label>
                    <input type="text" name="'.$name.'"
                        class="form-control" placeholder="'.$placeholder.'"
                        value="'.$value.'" '.$readonly.'>
                    </div>';
                if (!empty($legend)) {
                    $html.= '<span class="legend" style="display:block;">'.$legend.'</span>';
                }
                break;
            case 'select':
                $html.= '<label class="control-label">'.$label.'</label>

                    <select class="form-control lengow_select" name="'.$name.'">';
                foreach ($input['collection'] as $row) {
                    $selected =  $row['id'] == $value ? 'selected' : '';
                    $html.='<option value="'.$row['id'].'" '.$selected.'>'.$row['text'].'</option>';
                }
                $html.= '</select><span class="legend" style="display:block;">'.$legend.'</span></div>';
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
                        </div>';
                if (!empty($legend)) {
                    $html.= '<span class="legend" style="display:block;">'.$legend.'</span>';
                }
                $html.= '</div>';
                break;
        }
        return $html;
    }

    /**
    * Save Lengow settings
    *
    * @param array $checkboxKeys Lengow checkbox
    */
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
                    foreach ($value as $idShop => $shopValue) {
                        if (isset($this->fields[$key]['type']) &&
                            $this->fields[$key]['type'] == 'checkbox' && $shopValue == 'on') {
                            $shopValue = true;
                        }
                        $this->checkAndLog($key, $shopValue, $idShop);
                        LengowConfiguration::updateValue($key, $shopValue, false, null, $idShop);
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
            $idShop = $shop['id_shop'];
            foreach ($this->fields as $key => $value) {
                if (!in_array($key, $checkboxKeys)) {
                    continue;
                }
                if ($value['type'] == 'checkbox' && isset($value['shop']) && $value['shop']) {
                    if (!isset($_REQUEST[$key][$idShop])) {
                        $this->checkAndLog($key, false, $idShop);
                        LengowConfiguration::updateValue($key, false, false, null, $idShop);
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
                    $this->checkAndLog($key, false, $idShop);
                    LengowConfiguration::updateGlobalValue($key, false);
                }
            }
        }
    }

    /**
    * Check value and create a log if necessary
    *
    * @param string  $key    name of Lengow setting
    * @param mixed   $value  setting value
    * @param integer $idShop Prestashop shop id
    */
    public function checkAndLog($key, $value, $idShop = null)
    {
        if (is_null($idShop)) {
            $oldValue = LengowConfiguration::getGlobalValue($key);
        } else {
            $oldValue = LengowConfiguration::get($key, null, null, $idShop);
        }
        if (isset($this->fields[$key]['type']) && $this->fields[$key]['type'] == 'checkbox') {
            $value = (int)$value;
            $oldValue = (int)$oldValue;
        }
        if ($oldValue != $value) {
            if ($key == 'LENGOW_ACCESS_TOKEN' || $key == 'LENGOW_SECRET_TOKEN') {
                $value = preg_replace("/[a-zA-Z0-9]/", '*', $value);
                $oldValue = preg_replace("/[a-zA-Z0-9]/", '*', $oldValue);
            }
            if (!is_null($idShop)) {
                LengowMain::log(
                    'Setting',
                    LengowMain::setLogMessage(
                        'log.setting.setting_change_for_shop',
                        array(
                            'key'       => $key,
                            'old_value' => $oldValue,
                            'value'     => $value,
                            'shop_id'   => $idShop
                        )
                    )
                );
            } else {
                LengowMain::log(
                    'Setting',
                    LengowMain::setLogMessage(
                        'log.setting.setting_change',
                        array(
                            'key'       => $key,
                            'old_value' => $oldValue,
                            'value'     => $value
                        )
                    )
                );
            }
        }
    }
}
