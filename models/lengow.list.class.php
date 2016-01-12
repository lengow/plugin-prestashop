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
    protected $currentPage;
    protected $nbPerPage;
    protected $sql;
    protected $id;

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->fields_list = $params['fields_list'];
        $this->identifier = $params['identifier'];
        $this->selection = $params['selection'];
        $this->controller = $params['controller'];
        $this->shopId = $params['shop_id'];
        $this->currentPage = isset($params['current_page']) ? $params['current_page'] : 1;
        $this->nbPerPage = isset($params['nbPerPage']) ? $params['nbPerPage'] : 20;
        $this->sql = $params['sql'];
    }

    /**
     * v3
     * Display Table Header
     * @return string
     */
    public function displayHeader()
    {
        $html ='<table class="lengow_table table table-bordered table-striped table-hover" id="table_'.$this->id.'">';
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

        $html.='<tr class="lengow_filter">';
        if ($this->selection) {
            $html.='<th width="20"></th>';
        }
        foreach ($this->fields_list as $key => $values) {
            if (isset($values['filter']) && $values['filter']) {
                if (isset($_REQUEST['table_'.$this->id][$key])) {
                    $value = $_REQUEST['table_'.$this->id][$key];
                } else {
                    $value = '';
                }
                $html .= '<th><input type="text" name="table_'.$this->id.'[' . $key . ']" value="'.$value.'" /></th>';
            } elseif (isset($values['button_search']) && $values['button_search']) {
                $html .= '<th><input type="submit" value="Search" /></th>';
            } else {
                $html .= '<th></th>';
            }
        }
        $html.='</tr>';
        $html.='</thead>';
        return $html;
    }

    /**
     * v3
     * Display Table Content
     * @return string
     */
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

    /**
     * v3
     * Display Table Footer
     * @return string
     */
    public function displayFooter()
    {
        $html='</table>';
        return $html;
    }

    /**
     * v3
     * Display Table (Header + Content + Footer)
     * @return string
     */
    public function display()
    {
        $lengow_link = new LengowLink();
        $html= '<form id="form_table_'.$this->id.'" class="lengow_form_table"
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller).'">';
        $html.= $this->displayHeader().$this->displayContent().$this->displayFooter();
        $html.= '</form>';
        return $html;
    }

    /**
     * v3
     * Execute Queries (Collection + Total)
     * @return mixed
     */
    public function executeQuery()
    {
        $sql = $this->buildQuery();
        $sqlTotal = $this->buildQuery(true);

        $this->collection = Db::getInstance()->executeS($sql, true, false);
        $this->total = Db::getInstance()->getValue($sqlTotal, false);
        return $this->collection;
    }

    /**
     * v3
     * Find value by key in fieldlist
     * @param $keyToSeach key search in field list
     * @return boolean
     */
    public function findValueByKey($keyToSeach)
    {
        foreach ($this->fields_list as $key => $value) {
            if ($keyToSeach == $key) {
                return $value;
            }
        }
        return false;
    }

    /**
     * v3
     * Build Query
     * @param bool $total Execute Total Query
     * @return string sql query
     */
    public function buildQuery($total = false)
    {
        $where = $this->sql["where"];
        if (isset($_REQUEST['table_'.$this->id])) {
            foreach ($_REQUEST['table_'.$this->id] as $key => $value) {
                if (strlen($value)>0) {
                    if ($fieldValue = $this->findValueByKey($key)) {
                        $where[] = ' '.pSQL($fieldValue['filter_key']).' LIKE "%'.pSQL($value).'%"';
                    }
                }
            }
        }
        if ($total) {
            $sql = 'SELECT COUNT(*) as total';
        } else {
            $sql = 'SELECT '.join(', ', $this->sql["select"]);
        }
        $sql.= ' '.$this->sql["from"];
        if ($this->sql["join"]) {
            $sql.= join(' ', $this->sql["join"]);
        }
        if ($where) {
            $sql .= ' WHERE ' . join(' AND ', $where);
        }
        if (!$total) {
            if ($this->currentPage < 1) {
                $this->currentPage = 1;
            }
            $sql.= ' LIMIT '.($this->currentPage-1)* $this->nbPerPage.','.$this->nbPerPage;
        }
        return $sql;
    }

    public function updateCollection($collection)
    {
        $this->collection = $collection;
    }

    public function renderPagination($params = array())
    {
        $nav_class = isset($params["nav_class"]) ? $params["nav_class"] : '';

        $lengow_link = new LengowLink();
        $totalPage = ceil($this->total / $this->nbPerPage);
        $html = '<nav id="nav_'.$this->id.'" class="'.$nav_class.'"><ul class="lengow_pagination">';
        for ($i = 1; $i <= $totalPage; $i++) {
            $html.= '<li>';
            $class = ($i == $this->currentPage) ? 'disabled' : '';
            $html.= '<li class="'.$class.'"><a href="#"
            data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller).'&p='.$i.'">'.$i.'</a></li>';
            $html.= '</li>';
        }
        $html.= '</ul><nav>';
        return $html;
    }
}
