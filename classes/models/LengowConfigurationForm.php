<?php
/**
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
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2021 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * Lengow Configuration Form Class
 */
class LengowConfigurationForm
{
    /* Configuration form type */
    const TYPE_TEXT = 'text';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_SELECT = 'select';
    const TYPE_DAY = 'day';
    const TYPE_OPTIONS ='options';

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
     *
     * @param array $params construct parameters
     */
    public function __construct($params)
    {
        $this->fields = isset($params['fields']) ? $params['fields'] : false;
        $this->locale = new LengowTranslation();
    }

    /**
     * Construct Lengow setting input for shop
     *
     * @param integer $idShop PrestaShop shop id
     * @param array $displayKeys names of Lengow setting
     *
     * @return string
     */
    public function buildShopInputs($idShop, $displayKeys)
    {
        $html = '';
        foreach ($displayKeys as $key) {
            if (isset($this->fields[$key], $this->fields[$key][LengowConfiguration::PARAM_SHOP])
                && $this->fields[$key][LengowConfiguration::PARAM_SHOP]
            ) {
                $html .= $this->input($key, $this->fields[$key], $idShop);
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
                if (!isset($this->fields[$key][LengowConfiguration::PARAM_SHOP])
                    || !$this->fields[$key][LengowConfiguration::PARAM_SHOP]
                ) {
                    $html .= $this->input($key, $this->fields[$key]);
                }
            }
        }
        return $html;
    }

    /**
     * Get lengow input
     *
     * @param string $key name of Lengow setting
     * @param array $input all Lengow settings
     * @param integer|null $idShop PrestaShop shop id
     *
     * @return string
     */
    public function input($key, $input, $idShop = null)
    {
        $html = '';
        if ($idShop) {
            $name = $key . '[' . $idShop . ']';
            $value = LengowConfiguration::get($key, null, null, $idShop);
        } else {
            $name = $key;
            $value = LengowConfiguration::getGlobalValue($key);
        }
        $inputType = isset($input[LengowConfiguration::PARAM_TYPE])
            ? $input[LengowConfiguration::PARAM_TYPE]
            : self::TYPE_TEXT;
        $legend = isset($input[LengowConfiguration::PARAM_LEGEND]) ? $input[LengowConfiguration::PARAM_LEGEND] : '';
        $label = isset($input[LengowConfiguration::PARAM_LABEL]) ? $input[LengowConfiguration::PARAM_LABEL] : '';
        $placeholder = isset($input[LengowConfiguration::PARAM_PLACEHOLDER])
            ? $input[LengowConfiguration::PARAM_PLACEHOLDER]
            : '';
        $html .= '<div class="form-group ' . Tools::strtolower($key) . '"'
            . ($idShop ? ' data-id_shop="' . $idShop . '"' : '') . '>';
        switch ($inputType) {
            case self::TYPE_CHECKBOX:
                $checked = $value ? 'checked' : '';
                $html .= '<div class="lgw-switch ' . $checked . '"><label><div><span></span>';
                $html .= '<input name="' . $name . '" type="checkbox" ' . $checked . ' >';
                $html .= '</div>' . $label;
                $html .= '</label></div></div>';
                if (!empty($legend)) {
                    $html .= '<span class="legend blue-frame" style="display:block;">' . $legend . '</span>';
                }
                break;
            case self::TYPE_TEXT:
                $html .= '<label class="control-label">' . $label . '</label>
                    <input type="text"
                           name="' . $name . '"
                           class="form-control" placeholder="' . $placeholder . '"
                           value="' . $value . '">
                    </div>';
                if (!empty($legend)) {
                    $html .= '<span class="legend blue-frame" style="display:block;">' . $legend . '</span>';
                }
                break;
            case self::TYPE_SELECT:
                $html .= '<label class="control-label">' . $label . '</label>
                    <select class="form-control lengow_select" name="' . $name . '">';
                foreach ($input[LengowConfiguration::PARAM_COLLECTION] as $row) {
                    $selected = $row['id'] == $value ? 'selected' : '';
                    $html .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['text'] . '</option>';
                }
                $html .= '</select>';
                if (!empty($legend)) {
                    $html .= '<span class="legend blue-frame" style="display:block;">' . $legend . '</span>';
                }
                $html .= '</div>';
                break;
            case self::TYPE_DAY:
                $html .= '<label class="control-label">' . $label . '</label>
                        <div class="input-group">
                            <input type="number"
                                   name="' . $name . '"
                                   class="form-control"
                                   value="' . $value . '"
                                   min="' . (LengowImport::MIN_INTERVAL_TIME / 86400) . '"
                                   max="' . (LengowImport::MAX_INTERVAL_TIME / 86400) . '">
                            <div class="input-group-addon">
                                <div class="unit">' . $this->locale->t('order_setting.screen.nb_days') . '</div>
                            </div>
                            <div class="clearfix"></div>
                        </div>';
                if (!empty($legend)) {
                    $html .= '<span class="legend blue-frame" style="display:block;">' . $legend . '</span>';
                }
                $html .= '</div>';
                break;
            case self::TYPE_OPTIONS:
                $html .= '<label class="control-label">' . $label . '</label>
                              <select class="form-control lengow_select" name="' . $name . '">
                                <option value=".io" ' . ($value == '.io' ? 'selected' : '') . '>Prod</option>
                                <option value=".net" ' . ($value == '.net' ? 'selected' : '') . '>Preprod</option>
                              </select>';
                if (!empty($legend)) {
                    $html .= '<span class="legend blue-frame" style="display:block;">' . $legend . '</span>';
                }
                $html .= '</div>';
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
        try {
            $sql = 'SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop WHERE active = 1';
            $shopCollection = Db::getInstance()->ExecuteS($sql);
        } catch (PrestaShopDatabaseException $e) {
            $shopCollection = array(array('id_shop' => 1));
        }
        foreach ($_REQUEST as $key => $value) {
            if (isset($this->fields[$key])) {
                if (isset($this->fields[$key][LengowConfiguration::PARAM_SHOP])
                    && $this->fields[$key][LengowConfiguration::PARAM_SHOP]
                ) {
                    foreach ($value as $idShop => $shopValue) {
                        if (isset($this->fields[$key][LengowConfiguration::PARAM_TYPE])
                            && $this->fields[$key][LengowConfiguration::PARAM_TYPE] === self::TYPE_CHECKBOX
                            && $shopValue === 'on'
                        ) {
                            $shopValue = 1;
                        }
                        $this->checkAndLog($key, $shopValue, $idShop);
                        $shopValue = $shopValue === '' ? null : $shopValue;
                        LengowConfiguration::updateValue($key, $shopValue, false, null, $idShop);
                    }
                } elseif (is_array($value)) {
                    $this->checkAndLog($key, implode(',', $value));
                    LengowConfiguration::updateGlobalValue($key, implode(',', $value));
                } else {
                    if (isset($this->fields[$key][LengowConfiguration::PARAM_TYPE])
                        && $this->fields[$key][LengowConfiguration::PARAM_TYPE] === self::TYPE_CHECKBOX
                        && $value === 'on'
                    ) {
                        $value = 1;
                    }
                    $this->checkAndLog($key, $value);
                    $value = $value === '' ? null : $value;
                    LengowConfiguration::updateGlobalValue($key, $value);
                }
            }
        }
        foreach ($shopCollection as $shop) {
            $idShop = $shop['id_shop'];
            foreach ($this->fields as $key => $value) {
                if (!in_array($key, $checkboxKeys, true)) {
                    continue;
                }
                if ($value[LengowConfiguration::PARAM_TYPE] === self::TYPE_CHECKBOX
                    && isset($value[LengowConfiguration::PARAM_SHOP])
                    && $value[LengowConfiguration::PARAM_SHOP]
                ) {
                    if (!isset($_REQUEST[$key][$idShop])) {
                        $this->checkAndLog($key, 0, $idShop);
                        LengowConfiguration::updateValue($key, 0, false, null, $idShop);
                    }
                }
            }
        }
        foreach ($this->fields as $key => $value) {
            if (!in_array($key, $checkboxKeys, true)) {
                continue;
            }
            if ((!isset($value[LengowConfiguration::PARAM_SHOP]) || !$value[LengowConfiguration::PARAM_SHOP])) {
                if (!isset($_REQUEST[$key])) {
                    $this->checkAndLog($key, 0);
                    LengowConfiguration::updateGlobalValue($key, 0);
                }
            }
        }
    }

    /**
     * Check value and create a log if necessary
     *
     * @param string $key name of Lengow setting
     * @param mixed $value setting value
     * @param integer $idShop PrestaShop shop id
     */
    public function checkAndLog($key, $value, $idShop = null)
    {
        if (array_key_exists($key, $this->fields)) {
            $setting = $this->fields[$key];
            if ($idShop === null) {
                $oldValue = LengowConfiguration::getGlobalValue($key);
            } else {
                $oldValue = LengowConfiguration::get($key, null, null, $idShop);
            }
            if (isset($setting[LengowConfiguration::PARAM_TYPE])
                && $setting[LengowConfiguration::PARAM_TYPE] === self::TYPE_CHECKBOX
            ) {
                $value = (int) $value;
                $oldValue = (int) $oldValue;
            }
            if ($oldValue != $value) {
                if (isset($setting[LengowConfiguration::PARAM_SECRET])
                    && $setting[LengowConfiguration::PARAM_SECRET]
                ) {
                    $value = preg_replace("/[a-zA-Z0-9]/", '*', $value);
                    $oldValue = preg_replace("/[a-zA-Z0-9]/", '*', $oldValue);
                }
                if ($idShop !== null) {
                    LengowMain::log(
                        LengowLog::CODE_SETTING,
                        LengowMain::setLogMessage(
                            'log.setting.setting_change_for_shop',
                            array(
                                'key' => LengowConfiguration::$genericParamKeys[$key],
                                'old_value' => $oldValue,
                                'value' => $value,
                                'shop_id' => $idShop,
                            )
                        )
                    );
                } else {
                    LengowMain::log(
                        LengowLog::CODE_SETTING,
                        LengowMain::setLogMessage(
                            'log.setting.setting_change',
                            array(
                                'key' => LengowConfiguration::$genericParamKeys[$key],
                                'old_value' => $oldValue,
                                'value' => $value,
                            )
                        )
                    );
                }
                // save last update date for a specific settings (change synchronisation interval time)
                if (isset($setting[LengowConfiguration::PARAM_UPDATE])
                    && $setting[LengowConfiguration::PARAM_UPDATE]
                ) {
                    LengowConfiguration::updateGlobalValue(LengowConfiguration::LAST_UPDATE_SETTING, time());
                }
                // reset the authorization token when a configuration parameter is changed
                if (isset($setting[LengowConfiguration::PARAM_RESET_TOKEN])
                    && $setting[LengowConfiguration::PARAM_RESET_TOKEN]
                ) {
                    LengowConfiguration::resetAuthorizationToken();
                }
            }
        }
    }
}
