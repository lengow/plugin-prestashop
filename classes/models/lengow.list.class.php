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
    protected $selectionCondition;
    protected $controller;
    protected $shopId;
    protected $currentPage;
    protected $nbPerPage;
    protected $nbMaxPage;
    protected $sql;
    protected $id;
    protected $ajax;
    protected $context;

    public function __construct($params)
    {
        $this->id = $params['id'];
        $this->fields_list = $params['fields_list'];
        $this->identifier = $params['identifier'];
        $this->selection = $params['selection'];
        $this->selectionCondition = isset($params['selection_condition']) ? $params['selection_condition'] : false ;
        $this->controller = $params['controller'];
        $this->shopId = $params['shop_id'];
        $this->currentPage = isset($params['current_page']) ? $params['current_page'] : 1;
        $this->nbPerPage = isset($params['nbPerPage']) ? $params['nbPerPage'] : 20;
        $this->sql = $params['sql'];
        $this->ajax = isset($params['ajax']) ? (bool)$params['ajax'] : false;

        $this->context = Context::getContext();
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
            $html .= '<th>';
            if (isset($values['filter']) && $values['filter']) {
                $type = isset($values['filter_type']) ? $values['filter_type'] : 'text';
                $name = 'table_'.$this->id.'[' . $key . ']';
                if (isset($_REQUEST['table_'.$this->id][$key])) {
                    $value = $_REQUEST['table_'.$this->id][$key];
                } else {
                    $value = '';
                }
                switch ($type) {
                    case 'text':
                        $html .= '<input type="text" name="'.$name.'" value="'.$value.'" />';
                        break;
                    case 'select':
                        $html.='<select class="form-control" name="'.$name.'">';
                        $html.='<option value="" '.($value ? 'selected' : '').'></option>';
                        foreach ($values['filter_collection'] as $row) {
                            $selected =  $row['id'] == $value ? 'selected' : '';
                            $html.='<option value="'.$row['id'].'" '.$selected.'>'.$row['text'].'</option>';
                        }
                        $html.= '</select>';
                        break;
                }
            } elseif (isset($values['button_search']) && $values['button_search']) {
                $html .= '<input type="submit" value="Search" />';
            }
            $html .= '</th>';
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
                if ($this->selectionCondition) {
                    if ($item[$this->selectionCondition] > 0) {
                        $html.='<td><input type="checkbox" class="lengow_selection"
                    name="selection['.$item[$this->identifier].']" value="1"></td>';
                    } else {
                        $html.='<td></td>';
                    }
                } else {
                    $html.='<td><input type="checkbox" class="lengow_selection"
                    name="selection['.$item[$this->identifier].']" value="1"></td>';
                }
            }
            foreach ($this->fields_list as $key => $values) {
                if (isset($values['display_callback'])) {
                    $value = call_user_func_array($values['display_callback'], array($key, $item[$key]));
                } else {
                    if (isset($values['type'])) {
                        switch ($values["type"]) {
                            case 'date':
                                $value = Tools::dateFormat(
                                    array('date' => $item[$key], 'full' => true),
                                    $this->context->smarty
                                );
                                break;
                            case 'price':
                                $value = Tools::displayPrice($item[$key]);
                                break;
                            case 'switch_product':
                                $value = '<input type="checkbox" data-size="mini" data-on-text="Yes" data-off-text="No"
                               name="lengow_product_selection['.$item[$this->identifier].']"
                               class="lengow_switch lengow_switch_product
                               lengow_product_selection_'.$item[$this->identifier].'"
                               data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'"
                               data-action="select_product"
                               data-id_shop="'.$this->shopId.'"
                               data-id_product="'.$item[$this->identifier].'"
                               value="1" '.($item[$key] ? 'checked="checked"' : '').'/>';
                                break;
                            case 'flag_country':
                                if ($item[$key]) {
                                    $value = '<img src="/modules/lengow/views/img/flag/'.
                                        Tools::strtoupper($item[$key]).'.png" />';
                                } else {
                                    $value = '';
                                }
                                break;
                            case 'log_status':
                                if ($item[$key]) {

                                    if ($item[$key] == '2') {
                                        $value = '<i class="fa fa-info-circle lengow_red lengow_link_tooltip"
                                    data-original-title="test"
                                    ></i>';
                                        $value.= ' <a href="#"
                                    data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'"
                                    data-action="re_send"
                                    data-order="'.$item[$this->identifier].'"
                                    data-type="'.$item[$key].'"
                                    >Re Send</a>';
                                    } else {
                                        $value = '<i class="fa fa-info-circle lengow_red lengow_link_tooltip"
                                    data-original-title="test"
                                    ></i>';
                                        $value.= ' <a href="#"
                                    data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'"
                                    data-action="re_import"
                                    data-order="'.$item[$this->identifier].'"
                                    data-type="'.$item[$key].'"
                                    >Re Import</a>';
                                    }
                                } else {
                                    $value = '<i class="fa fa-circle lengow_green"></i>';
                                }
                                break;
                            default:
                                $value = $item[$key];
                        }
                    } else {
                        $value = $item[$key];
                    }
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
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'">';
        $html.= '<input type="hidden" name="p" value="'.$this->currentPage.'" />';
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
        $this->nbMaxPage = ceil($this->total / $this->nbPerPage);
        $this->paginationFrom = ($this->currentPage-1) * $this->nbPerPage + 1;
        if ($this->total == 0) {
            $this->paginationFrom = 0;
        }
        $this->paginationTo = $this->paginationFrom + $this->nbPerPage - 1;
        if ($this->currentPage >= $this->nbMaxPage) {
            $this->paginationTo = $this->total;
        }
        if ($this->nbMaxPage > 0 && $this->currentPage > $this->nbMaxPage) {
            $this->currentPage = $this->nbMaxPage;
            return $this->executeQuery();
        }
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
        $where = isset($this->sql["where"]) ? $this->sql["where"] : array();
        $having = array();
        if (isset($_REQUEST['table_'.$this->id])) {
            foreach ($_REQUEST['table_'.$this->id] as $key => $value) {
                if (Tools::strlen($value)>0) {
                    if ($fieldValue = $this->findValueByKey($key)) {
                        $type = isset($fieldValue['type']) ? $fieldValue['type'] : 'text';
                        switch ($type) {
                            case 'log_status':
                                switch ($value) {
                                    case 1:
                                        $having[] = ' '.pSQL($fieldValue['filter_key']).' IS NULL';
                                        break;
                                    case 2:
                                        $having[] = ' '.pSQL($fieldValue['filter_key']).' IS NOT NULL';
                                        break;
                                }
                                break;
                            case 'select':
                            case 'text':
                                $where[] = ' '.pSQL($fieldValue['filter_key']).' LIKE "%'.pSQL($value).'%"';
                                break;
                        }
                    }
                }
            }
        }
        if ($total) {
            $sql = 'SELECT COUNT(*) as total';
        } else {
            $sql = 'SELECT '.join(', ', $this->sql["select"]);
        }
        if (isset($this->sql['select_having']) && $this->sql['select_having']) {
            $sql.= ', '.join(',', $this->sql['select_having']);
        }
        $sql.= ' '.$this->sql["from"].' ';
        if ($this->sql["join"]) {
            $sql.= join(' ', $this->sql["join"]);
        }
        if ($where) {
            $sql .= ' WHERE ' . join(' AND ', $where);
        }
        if ($having) {
            $sql .= ' HAVING ' . join(' AND ', $having);
        }
        if (!$total) {
            if (isset($this->sql["order"])) {
                $sql .= ' ORDER BY '.$this->sql["order"];
            }
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
        $html = '<nav id="nav_'.$this->id.'" class="'.$nav_class.'">';

        $html.='<div class="lengow_pagination_total">';
        $html.= '<span class="lengow_number">'.$this->paginationFrom. '</span> -
        <span class="lengow_number">'.$this->paginationTo.'</span>
         sur <span class="lengow_number">'.$this->total.'</span>';
        $html.='</div>';

        $html.= '<ul class="lengow_pagination">';
        $class = ($this->currentPage == 1) ? 'disabled' : '';
        $html.= '<li><a href="#" class="'.$class.'"  data-page="'.($this->currentPage-1).'"
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.($this->currentPage-1).'"
        ><i class="fa fa-angle-left"></i></a></li>';
        $class = ($this->currentPage == $this->nbMaxPage) ? 'disabled' : '';
        $html.= '<li><a href="#" class="'.$class.'"  data-page="'.($this->currentPage+1).'"
        data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.($this->currentPage+1).'"
        ><i class="fa fa-angle-right"></i></a></li>';
        $html.= '</ul>';

        $html.= '<ul class="lengow_pagination">';
        if ($this->nbMaxPage > 10) {
            $showLastSeparation = false;

            $class = ($this->currentPage == 1) ? 'disabled' : '';
            $html.= '<li><a href="#" class="'.$class.'" data-page="1"
            data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p=1">1</a></li>';

            $from = $this->currentPage - 2;
            $to = $this->currentPage + 2;
            if ($from <= 2) {
                $from = 2;
                $to = $from + 5;
            } else {
                $html.= '<li><a href="#" class="disable">...</a></li>';
            }
            if ($to > ($this->nbMaxPage-1)) {
                $to = $this->nbMaxPage - 1;
            } else {
                if ($this->currentPage < ($this->nbMaxPage-4)) {
                    $showLastSeparation = true;
                }
            }
            for ($i = $from; $i <= $to; $i++) {
                $html .= '<li>';
                $class = ($i == $this->currentPage) ? 'disabled' : '';
                $html .= '<li class="' . $class . '"><a href="#" data-page="'.$i.'"
                data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.$i.'">'.
                    $i . '</a></li>';
                $html .= '</li>';
            }
            if ($showLastSeparation) {
                $html .= '<li><a href="#" class="disable">...</a></li>';
            }
            $class = ($this->currentPage == $this->nbMaxPage) ? 'disabled' : '';
            $html.= '<li><a href="#" class="'.$class.'"  data-page="'.$this->nbMaxPage.'"
            data-href="'.$lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax).'&p='.($this->nbMaxPage).'"
            >'.$this->nbMaxPage.'</a></li>';
        } else {
            for ($i = 1; $i <= $totalPage; $i++) {
                $html .= '<li>';
                $class = ($i == $this->currentPage) ? 'disabled' : '';
                $html .= '<li class="' . $class . '"><a href="#"  data-page="'.$i.'"
        data-href="' . $lengow_link->getAbsoluteAdminLink($this->controller, $this->ajax) . '&p=' . $i . '">' .
                    $i . '</a></li>';
                $html .= '</li>';
            }
        }
        $html.= '</ul></nav>';
        return $html;
    }
}
