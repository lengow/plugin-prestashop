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

class LengowList
{

    protected $fields_list;
    protected $collection;
    protected $total;
    protected $identifier;
    protected $selection;
    protected $controller;
    protected $shopId;


    public function __construct($params)
    {
        $this->collection = $params['collection'];
        $this->total = $params['total'];
        $this->fields_list = $params['fields_list'];
        $this->identifier = $params['identifier'];
        $this->selection = $params['selection'];
        $this->controller = $params['controller'];
        $this->shopId = $params['shop_id'];
    }

    public function displayHeader()
    {
        $html ='<table class="lengow_table table table-bordered table-striped table-hover">';
        $html.='<thead>';
        $html.='<tr>';
        if ($this->selection) {
            $html.='<th width="20"></th>';
        }
        foreach ($this->fields_list as $key => $values) {
            $width = isset($values['width']) ? 'width = "'.$values['width'].'"' : '';
            $html.='<th '.$width.'>'.$values['title'].'</th>';
        }
        $html.='</tr>';
        $html.='</thead>';
        return $html;
    }

    public function displayContent()
    {
        $lengow_link = new LengowLink();

        $html='<tbody>';
        foreach ($this->collection as $item) {
            $html.= '<tr>';
            if ($this->selection) {
                $html.='<td><input type="checkbox" name="selection['.$item[$this->identifier].']" value="1"></td>';
            }
            foreach ($this->fields_list as $key => $values) {
                if (isset($values['type'])) {
                    switch ($values["type"]) {
                        case "price":
                            $value = Tools::displayPrice($item[$key]);
                            break;
                        case "switch_product":
                            $value = '<input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_product_selection" class="lengow_switch lengow_switch_product"
                               data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller).'"
                               data-action="select_product"
                               data-id_shop="'.$this->shopId.'"
                               data-id_product="'.$item[$this->identifier].'"
                               value="1" '.($item[$key] ? 'checked="checked"' : '').'/>';
                            break;
                        case "image":
                            // item_id is the product id in a product image context, else it is the image id.
                            $item_id = isset($params['image_id']) ? $tr[$params['image_id']] : $id;
                            if ($params['image'] != 'p' || Configuration::get('PS_LEGACY_IMAGES')) {
                                $path_to_image = _PS_IMG_DIR_.$params['image'].'/'.$item_id.(isset($tr['id_image']) ? '-'.(int)$tr['id_image'] : '').'.'.$this->imageType;
                            } else {
                                $path_to_image = _PS_IMG_DIR_.$params['image'].'/'.Image::getImgFolderStatic($tr['id_image']).(int)$tr['id_image'].'.'.$this->imageType;
                            }
                            $this->_list[$index][$key] = ImageManager::thumbnail($path_to_image, $this->table.'_mini_'.$item_id.'_'.$this->context->shop->id.'.'.$this->imageType, 45, $this->imageType);

                            break;
                        default:
                            $value = $item[$key];
                    }
                } else {
                    $value = $item[$key];
                }
                $class = isset($values['class']) ? $values['class'] : '';

                $html.='<td class="'.$class.'">'.$value.'</td>';
            }
            $html.= '</tr>';
        }
        $html.='</tbody>';
        return $html;
    }

    public function displayFooter()
    {
        $html='</table>';
        return $html;
    }

    public function display()
    {
        return $this->displayHeader().$this->displayContent().$this->displayFooter();
    }

}
