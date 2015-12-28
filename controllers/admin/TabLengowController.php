<?php
/**
 * Copyright 2014 Lengow SAS.
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
 *  @author    Ludovic Drin <ludovic@lengow.com> Romain Le Polh <romain@lengow.com>
 *  @copyright 2014 Lengow SAS
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * The AdminTab Lengow Class.
 *
 * @author Ludovic Drin <ludovic@lengow.com>
 * @copyright 2013 Lengow SAS
 */
include_once PS_ADMIN_DIR.'/tabs/AdminProfiles.php';
include_once PS_ADMIN_DIR.'/tabs/AdminCatalog.php';

class AdminLengow14 extends AdminTab {

	protected $_pagination = array(1, 20, 50, 100, 300, 500, 1000);

	public function __construct()
	{
		$this->table = 'product';
		$this->className = 'Product';
		$this->lang = true;
		$this->edit = false;
		$this->delete = true;
		$this->view = 'noActionColumn';
		$this->duplicate = false;
		$this->noAdd = true;
		$this->_conf = array(
			1 => $this->l('Import success')
		);
		$this->fieldsDisplay = array(
			'id_product' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 20),
			'image' => array('title' => $this->l('Image'), 'align' => 'center', 'image' => 'p', 'width' => 45, 'orderby' => false, 'filter' => false, 'search' => false),
			'name' => array('title' => $this->l('Name'), 'width' => 220, 'filter_key' => 'b!name'),
			'reference' => array('title' => $this->l('Reference'), 'align' => 'center', 'width' => 20),
			'category' => array('title' => $this->l('Category'), 'width' => 70, 'align' => 'left', 'filter_key' => 'cl!name'),
			'price' => array('title' => $this->l('Original price'), 'width' => 70, 'price' => true, 'align' => 'right', 'filter_key' => 'a!price'),
			'price_final' => array('title' => $this->l('Final price'), 'width' => 70, 'price' => true, 'align' => 'right', 'havingFilter' => true, 'orderby' => false),
			'quantity' => array('title' => $this->l('Quantity'), 'width' => 30, 'align' => 'right', 'filter_key' => 'a!quantity', 'type' => 'decimal'),
			'isexport' => array('title' => $this->l('Status'), /*'active' => 'status',*/ 'search' => false, 'filter' => false, 'align' => 'center', /*'type' => 'bool',*/ 'orderby' => false));

		/* Join categories table */
		//$this->_category = AdminCatalog::getCurrentCategory();
		$this->_join = '
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = a.`id_product` AND i.`cover` = 1)
		LEFT JOIN `'._DB_PREFIX_.'lengow_product` lp ON lp.`id_product` = a.`id_product`
		LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = a.`id_product`)
		LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = a.`id_category_default`)
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category`)
		LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (a.`id_tax_rules_group` = tr.`id_tax_rules_group` AND tr.`id_country` = '.(int)Country::getDefaultCountryId().' AND tr.`id_state` = 0)
		LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)';
		//$this->_filter = 'AND cp.`id_category` = '.(int)($this->_category->id);
		$this->_select = 'cp.`position`, cl.`name` as `category`, i.`id_image`, (a.`price` * ((100 + (t.`rate`))/100)) AS `price_final`, if (lp.`id_product` > 0, 1, 0) AS `isexport`';
		$this->_group = 'GROUP BY a.`id_product`';

		parent::__construct();

		$module = Module::getInstanceByName('lengow');
		echo $module->display(_PS_MODULE_LENGOW_DIR_, 'views/templates/admin/header.tpl');
	}

	private function _cleanMetaKeywords($keywords)
	{
		if (!empty($keywords) && $keywords != '')
		{
			$out = array();
			$words = explode(',', $keywords);
			foreach ($words as $word_item)
			{
				$word_item = trim($word_item);
				if (!empty($word_item) && $word_item != '')
					$out[] = $word_item;
			}
			return ((count($out) > 0) ? implode(',', $out) : '');
		}
		else
			return '';
	}

	public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null)
	{
		$cookie = Context::getContext()->cookie;

		$orderByPriceFinal = (empty($orderBy) ? ($cookie->__get($this->table.'Orderby') ? $cookie->__get($this->table.'Orderby') : 'id_'.$this->table) : $orderBy);
		$orderWayPriceFinal = (empty($orderWay) ? ($cookie->__get($this->table.'Orderway') ? $cookie->__get($this->table.'Orderby') : 'ASC') : $orderWay);
		if ($orderByPriceFinal == 'price_final')
		{
			$orderBy = 'id_'.$this->table;
			$orderWay = 'ASC';
		}
		parent::getList($id_lang, $orderBy, $orderWay, $start, $limit);

		/* update product quantity with attributes ... */
		if ($this->_list)
		{
			$nb = count($this->_list);
			for ($i = 0; $i < $nb; $i++)
				Attribute::updateQtyProduct($this->_list[$i]);
			/* update product final price */
			for ($i = 0; $i < $nb; $i++)
				$this->_list[$i]['price_tmp'] = Product::getPriceStatic($this->_list[$i]['id_product'], true, null, 2, null, false, true, 1, true);
		}

		if ($orderByPriceFinal == 'price_final')
		{
			if (Tools::strtolower($orderWayPriceFinal) == 'desc')
				uasort($this->_list, 'cmpPriceDesc');
			else
				uasort($this->_list, 'cmpPriceAsc');
		}
		for ($i = 0; $this->_list && $i < $nb; $i++)
		{
			$this->_list[$i]['price_final'] = $this->_list[$i]['price_tmp'];
			unset($this->_list[$i]['price_tmp']);
		}
	}

	public function deleteVirtualProduct()
	{
		if (!($id_product_download = ProductDownload::getIdFromIdProduct((int)Tools::getValue('id_product'))) && !Tools::getValue('file'))
			return false;

		// case 1: product has been saved and product download entry has been created
		if ($id_product_download)
		{
			$productDownload = new ProductDownload((int)($id_product_download));
			return $productDownload->delete(true);
		}
		// case 2: product has not been created yet
		else
		{
			$file = Tools::getValue('file');
			if (file_exists(_PS_DOWNLOAD_DIR_.$file))
				return unlink(_PS_DOWNLOAD_DIR_.$file);
		}
	}

	/**
	* postProcess handle every checks before saving products information
	*
	* @param mixed $token
	* @return void
	*/
	public function postProcess($token = null)
	{
		$sep = DIRECTORY_SEPARATOR;
		include_once _PS_MODULE_DIR_.'lengow'.$sep.'lengow.php';
		//$lengow = new Lengow();
		//$cookie = Context::getContext()->cookie;
		if (Tools::getValue('publishproduct'))
			$this->processBulkPublish();
		if (Tools::getValue('unpublishproduct'))
			$this->processBulkUnpublish();
		if (Tools::getValue('lengowunpublishproduct'))
			LengowProduct::publish(Tools::getValue('lengowunpublishproduct'), 0);
		elseif (Tools::getValue('lengowpublishproduct'))
			LengowProduct::publish(Tools::getValue('lengowpublishproduct'));
		if (Tools::getValue('importorder'))
		{
			@set_time_limit(0);
			require_once _PS_MODULE_DIR_.'lengow'.$sep.'models'.$sep.'lengow.import.class.php';
			$import = new LengowImport();
			$import->force_log_output = false;
			$date_to = date('Y-m-d');
			$days = (integer)LengowCore::getCountDaysToImport();
			$date_from = date('Y-m-d', strtotime(date('Y-m-d').' -'.$days.'days'));
			$result = $import->exec('commands', array('dateFrom' => $date_from,
				'dateTo' => $date_to));
			if ($result && ($result['new'] > 0 || $result['update'] > 0))
				Tools::redirectAdmin('index.php?tab=AdminLengow14&conf=1&token='.($token ? $token : $this->token));
			else
				$this->_errors[] = Tools::displayError('No available order to import or update.');
		}
		parent::postProcess($token);
	}

	protected function processBulkPublish()
	{
		$products = Tools::getValue($this->table.'Box');
		if (is_array($products) && (count($products)))
			foreach ($products as $id_product)
				LengowProduct::publish($id_product);
	}

	protected function processBulkUnpublish()
	{
		$products = Tools::getValue($this->table . 'Box');
		if (is_array($products) && (count($products))) {
			foreach ($products as $id_product) {
				LengowProduct::publish($id_product, 0);
			}
		}
	}

	/**
	* displayList show ordered list of current category
	*
	* @param mixed $token
	* @return void
	*/
	public function displayList($token = null)
	{
		/* Display list header (filtering, pagination and column names) */
		$this->displayConf();
		$this->displayListHeader($token);
		if (!count($this->_list))
			echo '<tr><td class="center" colspan="'.(count($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';
		/* Show the content of the table */
		$this->displayListContent($token);
		/* Close list table and submit button */
		$this->displayListFooter($token);
	}

	/**
	* Display results in a table
	*
	* align  : determine value alignment
	* prefix : displayed before value
	* suffix : displayed after value
	* image  : object image
	* icon   : icon determined by values
	* active : allow to toggle status
	*/
	public function displayListContent($token = null)
	{
		$currentIndex = 'index.php?tab=AdminLengow14';
		$cookie = Context::getContext()->cookie;
		$currency = new Currency(_PS_CURRENCY_DEFAULT_);
		$id_category = 1; // default cat
		$irow = 0;
		if ($this->_list && isset($this->fieldsDisplay['position']))
		{
			$positions = array_map(create_function('$elem', 'return (int)($elem[\'position\']);'), $this->_list);
			sort($positions);
		}
		if ($this->_list)
		{
			$isCms = false;
			if (preg_match('/cms/Ui', $this->identifier))
				$isCms = true;
			$keyToGet = 'id_'.($isCms ? 'cms_' : '').'category'.(in_array($this->identifier, array('id_category', 'id_cms_category')) ? '_parent' : '');
			foreach ($this->_list as $tr)
			{
				$id = $tr[$this->identifier];
				echo '<tr'.(array_key_exists($this->identifier, $this->identifiersDnd) ? ' id="tr_'.(($id_category = (int)(Tools::getValue('id_'.($isCms ? 'cms_' : '').'category', '1'))) ? $id_category : '').'_'.$id.'_'.$tr['position'].'"' : '').($irow++ % 2 ? ' class="alt_row"' : '').' '.((isset($tr['color']) && $this->colorOnBackground) ? 'style="background-color: '.$tr['color'].'"' : '').'>
							<td class="center">';
				echo '<input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" />';
				echo '</td>';
				foreach ($this->fieldsDisplay as $key => $params)
				{
					$tmp = explode('!', $key);
					$key = isset($tmp[1]) ? $tmp[1] : $tmp[0];
					echo '
					<td '.(isset($params['position']) ? ' id="td_'.(isset($id_category) && $id_category ? $id_category : 0).'_'.$id.'"' : '').' class="'.((!isset($this->noLink) || !$this->noLink) ? 'pointer' : '').((isset($params['position']) && $this->_orderBy == 'position') ? ' dragH&&le' : '').(isset($params['align']) ? ' '.$params['align'] : '').'" ';
					if (!isset($params['position']) && (!isset($this->noLink) || !$this->noLink))
						echo ' onclick="document.location = \''.$currentIndex.'&'.$this->identifier.'='.$id.($this->view ? '&view' : '&update').$this->table.'&token='.($token != null ? $token : $this->token).'\'">'.(isset($params['prefix']) ? $params['prefix'] : '');
					else
						echo '>';
					if ($key == 'isexport')
					{
						$token = Tools::getAdminTokenLite('AdminLengow14');
						if ($tr[$key] == 0)
							echo '<a href="index.php?tab=AdminLengow14&lengowpublishproduct='.$id.'&token='.$token.'"><img src="'._PS_ADMIN_IMG_.'disabled.gif" /></a>';
						else
							echo '<a href="index.php?tab=AdminLengow14&lengowunpublishproduct='.$id.'&token='.$token.'"><img src="'._PS_ADMIN_IMG_.'enabled.gif" /></a>';
					}
					elseif (isset($params['active']) && isset($tr[$key]))
						$this->_displayEnableLink($token, $id, $tr[$key], $params['active'], Tools::getValue('id_category'), Tools::getValue('id_product'));
					elseif (isset($params['activeVisu']) && isset($tr[$key]))
						echo '<img src="../img/admin/'.($tr[$key] ? 'enabled.gif' : 'disabled.gif').'"
						alt="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" />';
					elseif (isset($params['position']))
					{
						if ($this->_orderBy == 'position' && $this->_orderWay != 'DESC')
						{
							echo '<a'.(!($tr[$key] != $positions[count($positions) - 1]) ? ' style="display: none;"' : '').' href="'.$currentIndex.
							'&'.$keyToGet.'='.(int)($id_category).'&'.$this->identifiersDnd[$this->identifier].'='.$id.'
									&way=1&position='.(int)($tr['position'] + 1).'&token='.($token != null ? $token : $this->token).'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'down' : 'up').'.gif"
									alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>';

							echo '<a'.(!($tr[$key] != $positions[0]) ? ' style="display: none;"' : '').' href="'.$currentIndex.
							'&'.$keyToGet.'='.(int)($id_category).'&'.$this->identifiersDnd[$this->identifier].'='.$id.'
									&way=0&position='.(int)($tr['position'] - 1).'&token='.($token != null ? $token : $this->token).'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'up' : 'down').'.gif"
									alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>';
						}
						else
							echo (int)($tr[$key] + 1);
					}
					elseif (isset($params['image']))
					{
						// item_id is the product id in a product image context, else it is the image id.
						$item_id = isset($params['image_id']) ? $tr[$params['image_id']] : $id;
						// If it's a product image
						if (isset($tr['id_image']) && _PS_VERSION_ >= '1.4.2.5')
						{
							$image = new Image((int)$tr['id_image']);
							$path_to_image = _PS_IMG_DIR_.$params['image'].'/'.$image->getExistingImgPath().'.'.$this->imageType;
						}
						else
							$path_to_image = _PS_IMG_DIR_.$params['image'].'/'.$item_id.(isset($tr['id_image']) ? '-'.(int)($tr['id_image']) : '').'.'.$this->imageType;

						echo cacheImage($path_to_image, $this->table.'_mini_'.$item_id.'.'.$this->imageType, 45, $this->imageType);
					}
					elseif (isset($params['icon']) && (isset($params['icon'][$tr[$key]]) || isset($params['icon']['default'])))
						echo '<img src="../img/admin/'.(isset($params['icon'][$tr[$key]]) ? $params['icon'][$tr[$key]] : $params['icon']['default'].'" alt="'.$tr[$key]).'" title="'.$tr[$key].'" />';
					elseif (isset($params['price']))
						echo Tools::displayPrice($tr[$key], (isset($params['currency']) ? Currency::getCurrencyInstance((int)($tr['id_currency'])) : $currency), false);
					elseif (isset($params['float']))
						echo rtrim(rtrim($tr[$key], '0'), '.');
					elseif (isset($params['type']) && $params['type'] == 'date')
						echo Tools::displayDate($tr[$key], (int)$cookie->id_lang);
					elseif (isset($params['type']) && $params['type'] == 'datetime')
						echo Tools::displayDate($tr[$key], (int)$cookie->id_lang, true);
					elseif (isset($tr[$key]))
					{
						$echo = ($key == 'price' ? round($tr[$key], 2) : isset($params['maxlength']) ? Tools::substr($tr[$key], 0, $params['maxlength']).'...' : $tr[$key]);
						echo isset($params['callback']) ? call_user_func_array(array($this->className, $params['callback']), array($echo, $tr)) : $echo;
					}
					else
						echo '--';
					echo (isset($params['suffix']) ? $params['suffix'] : '').
					'</td>';
				}

				if ($this->edit || $this->delete || ($this->view && $this->view !== 'noActionColumn'))
				{
					echo '<td class="center" style="white-space: nowrap;">';
					echo '</td>';
				}
				echo '</tr>';
			}
		}
	}

	/**
	* Close list table and submit button
	*/
	public function displayListFooter($token = null)
	{
		echo '</table>';
		echo '<p><input type="submit" class="button" name="publish'.$this->table.'" value="'.$this->l('Publish on Lengow').'" />  ';
		echo '<input type="submit" class="button" name="unpublish'.$this->table.'" value="'.$this->l('Unpublish on Lengow').'" /></p>';
		echo '</td></tr></table>';
		echo '<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" /></form>';
		if (isset($this->_includeTab) && count($this->_includeTab))
			echo '<br /><br />';
	}

}
