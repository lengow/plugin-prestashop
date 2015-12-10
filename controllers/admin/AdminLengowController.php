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
 * The Lengow's Admin Controller.
 *
 */

class AdminLengowController extends ModuleAdminController
{
	protected $id_current_category;

	/**
	* Construct the admin selection of products
	*/
	public function __construct()
	{
		$this->table 		  = 'product';
		$this->className 	  = 'LengowProduct';
		$this->template = 'layout.tpl';
		$this->lang		   = true;
		$this->lite_display = true;
		$this->explicitSelect = true;
		$this->meta_title = 'Lengow Products';
		$this->list_no_link   = true;
		$this->actions = array('lengowpublish', 'lengowunpublish');



		parent::__construct();

		$this->imageType = 'jpg';

		if (Tools::getValue('reset_filter_category'))
			$this->context->cookie->id_category_products_filter = false;
		if (Shop::isFeatureActive() && $this->context->cookie->id_category_products_filter)
		{
			$category = new Category((int)$this->context->cookie->id_category_products_filter);
			if (!$category->inShop())
			{
				$this->context->cookie->id_category_products_filter = false;
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts'));
			}
		}
		/* Join categories table */
		if ($id_category = (int)Tools::getValue('productFilter_cl!name'))
		{
			$this->_category = new Category((int)$id_category);
			$_POST['productFilter_cl!name'] = $this->_category->name[$this->context->language->id];
		}
		else
		{
			if ($id_category = (int)Tools::getValue('id_category'))
			{
				$this->id_current_category = $id_category;
				$this->context->cookie->id_category_products_filter = $id_category;
			}
			elseif ($id_category = $this->context->cookie->id_category_products_filter)
				$this->id_current_category = $id_category;
			if ($this->id_current_category)
				$this->_category = new Category((int)$this->id_current_category);
			else
				$this->_category = new Category();
		}

		$join_category = false;
		if (Validate::isLoadedObject($this->_category) && empty($this->_filter))
			$join_category = true;

		$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = a.`id_product` '.(!Shop::isFeatureActive() ? ' AND i.cover=1' : '').')
		LEFT JOIN `'._DB_PREFIX_.'lengow_product` lp ON lp.`id_product` = a.`id_product` ';

		if (Shop::isFeatureActive())
		{
			$alias = 'sa';
			$alias_image = 'image_shop';
			if (Shop::getContext() == Shop::CONTEXT_SHOP)
			{
				$this->_join .= ' JOIN `'._DB_PREFIX_.'product_shop` sa ON (a.`id_product` = sa.`id_product` AND sa.id_shop = '.(int)$this->context->shop->id.')
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON ('.pSQL($alias).'.`id_category_default` = cl.`id_category` AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = '.(int)$this->context->shop->id.')
				LEFT JOIN `'._DB_PREFIX_.'shop` shop ON (shop.id_shop = '.(int)$this->context->shop->id.')
				LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (image_shop.`id_image` = i.`id_image` AND image_shop.`cover` = 1 AND image_shop.id_shop='.(int)$this->context->shop->id.')';
			}
			else
			{
				$this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_shop` sa ON (a.`id_product` = sa.`id_product` AND sa.id_shop = a.id_shop_default)
				LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON ('.pSQL($alias).'.`id_category_default` = cl.`id_category` AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = a.id_shop_default)
				LEFT JOIN `'._DB_PREFIX_.'shop` shop ON (shop.id_shop = a.id_shop_default)
				LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (image_shop.`id_image` = i.`id_image` AND image_shop.`cover` = 1 AND image_shop.id_shop=a.id_shop_default)';
			}
			$this->_select .= 'shop.name as shopname, lp.`id_product` as `id_lengow_product`, ';
		}
		else
		{
			$alias = 'a';
			$alias_image = 'i';
			$this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON ('.pSQL($alias).'.`id_category_default` = cl.`id_category` AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = 1)';
		}

		$this->_select .= 'MAX('.pSQL($alias_image).'.id_image) id_image,';

		$this->_join .= ($join_category ? 'INNER JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = a.`id_product` AND cp.`id_category` = '.(int)$this->_category->id.')' : '').'
		LEFT JOIN `'._DB_PREFIX_.'stock_available` sav ON (sav.`id_product` = a.`id_product` AND sav.`id_product_attribute` = 0
		'.StockAvailable::addSqlShopRestriction(null, null, 'sav').') ';
		$this->_select .= 'cl.name `name_category` '.($join_category ? ', cp.`position`' : '').', '.pSQL($alias).'.`price`, 0 AS price_final, sav.`quantity` as sav_quantity, '.pSQL($alias).'.`active`, IFNULL(lp.`id_product`, 0) as `id_lengow_product`	';

		$this->_group = 'GROUP BY '.pSQL($alias).'.id_product';

		$this->fields_list = array();

		$this->fields_list['id_product'] = array(
			'title' => $this->l('ID'),
			'align' => 'center',
			'width' => 20
		);
		$this->fields_list['image'] = array(
			'title' => $this->l('Image'),
			'align' => 'center',
			'image' => 'p',
			'width' => 70,
			'orderby' => false,
			'filter' => false,
			'search' => false
		);
		$this->fields_list['name'] = array(
			'title' => $this->l('Name'),
			'filter_key' => 'b!name'
		);
		$this->fields_list['reference'] = array(
			'title' => $this->l('Reference'),
			'align' => 'left',
			'width' => 80
		);

		if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP)
			$this->fields_list['shopname'] = array(
				'title' => $this->l('Default shop:'),
				'width' => 230,
				'filter_key' => 'shop!name',
			);
		else
			$this->fields_list['name_category'] = array(
				'title' => $this->l('Category'),
				'width' => 'auto',
				'filter_key' => 'cl!name',
			);

		$this->fields_list['price'] = array(
			'title' => $this->l('Original price'),
			'width' => 90,
			'type' => 'price',
			'align' => 'right',
			'filter_key' => 'a!price'
		);
		$this->fields_list['price_final'] = array(
			'title' => $this->l('Final price'),
			'width' => 90,
			'type' => 'price',
			'align' => 'right',
			'havingFilter' => true,
			'orderby' => false
		);
		$this->fields_list['sav_quantity'] = array(
			'title' => $this->l('Quantity'),
			'width' => 90,
			'align' => 'right',
			'filter_key' => 'sav!quantity',
			'orderby' => true,
			'hint' => $this->l('This is the quantity available in the current shop/group.'),
		);
		$this->fields_list['id_lengow_product'] = array(
			'title' => $this->l('Lengow status'),
			'width' => 'auto',
			//'active' => 'status',
			'filter' => false,
			'search' => false,
			'align' => 'center',
			//'type' => 'bool',
			'callback' => 'getLengowStatus',
			'orderby' => false
		);

		if ((int)$this->id_current_category)
			$this->fields_list['position'] = array(
				'title' => $this->l('Position'),
				'width' => 70,
				'filter_key' => 'cp!position',
				'align' => 'center',
				'position' => 'position'
			);

		$this->bulk_actions = array('publish' => array('text' => $this->l('Publish to Lengow')),
									'unpublish' => array('text' => $this->l('Unpublish to Lengow')));

	}

	/**
	* Set media
	*
	* @return media
	*/
	public function setMedia()
	{
		$this->addCSS('/modules/lengow/views/css/lengow-admin.css');
		return parent::setMedia();
	}

	/**
	* Set the productlist
	*
	* @param integer $id_lang ID of lang
	* @param varchar $orderBy Order of list
	* @param varchar $orderWay Sens of list
	* @param integer $start Start
	* @param integer $limit Count limit of list
	* @param integer $id_lang_shop ID of lang'shop
	*/
	public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
	{
		$id_lang_shop = $id_lang_shop;
		$orderByPriceFinal = (empty($orderBy) ? ($this->context->cookie->__get($this->table.'Orderby') ? $this->context->cookie->__get($this->table.'Orderby') : 'id_'.$this->table) : $orderBy);
		$orderWayPriceFinal = (empty($orderWay) ? ($this->context->cookie->__get($this->table.'Orderway') ? $this->context->cookie->__get($this->table.'Orderby') : 'ASC') : $orderWay);
		if ($orderByPriceFinal == 'price_final')
		{
			$orderBy = 'id_'.$this->table;
			$orderWay = 'ASC';
		}
		parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

		/* update product quantity with attributes ...*/
		$nb = count($this->_list);
		if ($this->_list)
		{
			/* update product final price */
			for ($i = 0; $i < $nb; $i++)
			{
				// convert price with the currency from context
				$this->_list[$i]['price'] = Tools::convertPrice($this->_list[$i]['price'], $this->context->currency, true, $this->context);
				$this->_list[$i]['price_tmp'] = Product::getPriceStatic($this->_list[$i]['id_product'], true, null, 2, null, false, true, 1, true);
			}
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

	/**
	* Init content html
	*
	* @param $token Token
	*/
	public function initContent($token = null)
	{
		$token = $token;
		if ($id_category = (int)$this->id_current_category)
			self::$currentIndex .= '&id_category='.(int)$this->id_current_category;

		// If products from all categories are displayed, we don't want to use sorting by position
		if (!$id_category)
		{
			$this->_defaultOrderBy = $this->identifier;
			if ($this->context->cookie->{$this->table.'Orderby'} == 'position')
			{
				unset($this->context->cookie->{$this->table.'Orderby'});
				unset($this->context->cookie->{$this->table.'Orderway'});
			}
		}
		if (!$id_category)
			$id_category = 1;
		$this->tpl_list_vars['is_category_filter'] = (bool)$this->id_current_category;

		// Generate category selection tree
		$helper = new Helper();
		$this->tpl_list_vars['category_tree'] = $helper->renderCategoryTree(null, array((int)$id_category), 'categoryBox', true, false, array(), false, true);
		if (isset($helper->actions))
			$helper->actions['unpublish'];

		// used to build the new url when changing category
		$this->tpl_list_vars['base_url'] = preg_replace('#&id_category=[0-9]*#', '', self::$currentIndex).'&token='.$this->token;

		parent::initContent();
	}

	/**
	* Get status Lengow of current line
	*
	* @param text $echo Block html
	* @param object $row Order of list
	*
	* @return boolean Is selected
	*/
	public function getLengowStatus($echo, $row)
	{
		$echo = $echo; // Prestashop validator
		$token = Tools::getAdminTokenLite('AdminLengow', Context::getContext());
		if ($row['id_lengow_product'] == 0)
			return '<a href="index.php?controller=AdminLengow&publish='.$row['id_product'].'&token='.$token.'"><img src="'._PS_ADMIN_IMG_.'disabled.gif" /></a>';
		else
			return '<a href="index.php?controller=AdminLengow&unpublish='.$row['id_product'].'&token='.$token.'"><img src="'._PS_ADMIN_IMG_.'enabled.gif" /></a>';
		return $row->id_lengow_product > 0 ? true : false;
	}

	/**
	* Get status Lengow of current line
	*
	* @param text $echo Block html
	* @param object $row Order of list
	*
	* @return boolean Is selected
	*/
	public function renderList()
	{
		$this->addRowAction('lengowunpublish');
		return parent::renderList();
	}

	/**
	* Publish selected products to Lengow
	*/
	protected function processBulkPublish()
	{
		$products = Tools::getValue($this->table.'Box');
		if (is_array($products) && (count($products)))
			foreach ($products as $id_product)
				LengowProduct::publish($id_product);
	}

	/**
	* Unpublish selected products to Lengow
	*/
	protected function processBulkUnpublish()
	{
		$products = Tools::getValue($this->table.'Box');
		if (is_array($products) && (count($products)))
			foreach ($products as $id_product)
				LengowProduct::publish($id_product, 0);
	}

	/**
	* postProcess handle every checks before saving products information
	*
	* @param mixed $token
	* @return void
	*/
	public function postProcess($token = null)
	{
		if (Tools::getValue('unpublish'))
			LengowProduct::publish(Tools::getValue('unpublish'), 0);
		elseif (Tools::getValue('publish'))
			LengowProduct::publish(Tools::getValue('publish'));
		if (Tools::getValue('importorder'))
		{
			@set_time_limit(0);
			$sep = DIRECTORY_SEPARATOR;
			require_once _PS_MODULE_DIR_.'lengow'.$sep.'models'.$sep.'lengow.import.class.php';
			$import = new LengowImport();
			$import->force_log_output = false;
			$date_to = date('Y-m-d');
			$days = (integer)LengowCore::getCountDaysToImport();
			$date_from = date('Y-m-d', strtotime(date('Y-m-d').' -'.$days.'days'));
			$result = $import->exec('commands', array(
						'dateFrom' => $date_from,
						'dateTo' => $date_to
						));
			if ($result && ($result['new'] > 0 || $result['update'] > 0))
			{
				if ($result['new'] > 0)
					$this->confirmations[] = sprintf($this->l('Import %d order%s'), $result['new'], $result['new'] > 1 ? 's' : '');
				if ($result['update'] > 0)
					$this->confirmations[] = sprintf($this->l('Updated %d order%s'), $result['update'], $result['update'] > 1 ? 's' : '');
			}
			else
				$this->errors[] = Tools::displayError('No available order to import or update.');
		}

		// avoids Prestashop from crashing by precising the table alias for the "quantity" field
		if (Tools::getValue('productOrderby') == 'quantity')
		{
			if ($_POST['productOrderby'])
				$_POST['productOrderby'] = 'sav.quantity';
			else
				$_GET['productOrderby'] = 'sav.quantity';
		}

		parent::postProcess($token);
	}

	/**
	* Init Toolbar
	*/
	public function initToolbar()
	{
		parent::initToolbar();
		unset($this->toolbar_btn['new']);
		/*$this->toolbar_btn['importlengow'] = array(
				'href' => $this->context->link->getAdminLink('AdminLengow', true).'&importorder=1',
				'desc' => $this->l('Import orders from lengow')
			);*/
		$this->context->smarty->assign('toolbar_scroll', 1);
		$this->context->smarty->assign('show_toolbar', 1);
		$this->context->smarty->assign('toolbar_btn', $this->toolbar_btn);
	}

	/**
	* Ajax action to update flow's conf
	*
	* @return json Return parameters
	*/
	public function displayAjaxUpdateFlow()
	{
		@set_time_limit(0);
		$sep = DIRECTORY_SEPARATOR;
		require_once _PS_MODULE_DIR_.'lengow'.$sep.'models'.$sep.'lengow.connector.class.php';
		$lengow_connector = new LengowConnector((integer)LengowCore::getIdCustomer(), LengowCore::getTokenCustomer());
		$params = 'format='.Tools::getValue('format');
		$params .= '&mode='.Tools::getValue('mode');
		$params .= '&all='.Tools::getValue('all');
		$params .= '&shop='.Tools::getValue('shop');
		$params .= '&cur='.Tools::getValue('cur');
		$params .= '&lang='.Tools::getValue('lang');
		$is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
		$shop_url = new ShopUrl((integer)Tools::getValue('shop'));
		$new_flow = 'http'.$is_https.'://'.$shop_url->domain.__PS_BASE_URI__.'modules/lengow/webservice/export.php?'.$params;
		$args = array(
				'idClient' => LengowCore::getIdCustomer(),
				'idGroup' => LengowCore::getGroupCustomer(),
				'urlFlux' => $new_flow
				);
		$data_flows = get_object_vars(Tools::jsonDecode(Configuration::get('LENGOW_FLOW_DATA')));
		if ($id_flow = Tools::getValue('idFlow'))
		{
			$args['idFlux'] = $id_flow;
			$data_flows[$id_flow] = array(
				'format' => Tools::getValue('format') ,
				'mode' => Tools::getValue('mode') == 'yes' ? 1 : 0,
				'all' => Tools::getValue('all') == 'yes' ? 1 : 0 ,
				'currency' => Tools::getValue('cur') ,
				'shop' => Tools::getValue('shop') ,
				'language' => Tools::getValue('lang') ,
			);
			Configuration::updateValue('LENGOW_FLOW_DATA', Tools::jsonEncode($data_flows));
		}
		if ($lengow_connector->api('updateRootFeed', $args))
			echo Tools::jsonEncode(array('return' => true, 'flow' => $new_flow));
		else
			echo Tools::jsonEncode(array('return' => false));
	}

	public function displayAjaxReimportOrder()
	{
		@set_time_limit(0);
		$sep = DIRECTORY_SEPARATOR;
		require_once LengowCore::getLengowFolder().$sep.'models'.$sep.'lengow.import.class.php';

		$error = false;
		$order_id = Tools::getValue('id_order');
		// make sure order is from Lengow
		if (!LengowOrder::isFromLengow($order_id))
			echo Tools::jsonEncode(array('status' => 'error', 'msg' => 'Order is not a Lengow order'));
		$order = new LengowOrder($order_id);
		// disable order
		LengowOrder::disable($order->id);
		// suppress log to allow order to be reimported
		LengowLog::deleteLog($order->id_lengow);

		// start import
		$import = new LengowImport($order->id_lengow, $order->id_feed_lengow);
		$new_order_id = $import->exec();

		if ($new_order_id != false && is_numeric($new_order_id))
		{
			// Cancel Order
			$order->setStateToError();
			// delete log & disable order from Lengow table to allow its reimportation
			// Redirect to the new order
			$new_order_url = 'index.php?controller=AdminOrders&id_order='.$new_order_id.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders');
			$reimport_message = sprintf('You can see the new order by clicking here : <a href=\'%s\'>View Order %s</a>', $new_order_url, $new_order_id);
		}
		else
		{
			$error = true;
			$reimport_message = $this->l('Error during import');
			$this->context->controller->warnings[] = $this->l('Error during import');
		}
		$result = array(
			'status' => ($error == false) ? 'success' : 'error',
			'msg' => $reimport_message,
			'new_order_url' => isset($new_order_url) ? $new_order_url : '',
			'new_order_id' => isset($new_order_id) ? $new_order_id : '',
		);
		if (isset($new_order_url))
			header($new_order_url);
		else
			echo Tools::jsonEncode($result);
	}
}