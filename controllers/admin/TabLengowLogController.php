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
 * @author Romain Le Polh <romain@lengow.com>
 * @copyright 2013 Lengow SAS
 */
class AdminLengowLog14 extends AdminTab {

	protected $_pagination = array(1, 20, 50, 100, 300);

	public function __construct()
	{
		$this->table = 'lengow_logs_import';
		$this->className = 'LengowLog';
		$this->lang = false;
		$this->edit = false;
		$this->delete = false;
		$this->view = false;
		$this->duplicate = false;
		$this->noAdd = true;

		$this->fieldsDisplay = array(
			'lengow_order_id' => array(
				'title' => $this->l('Lengow Order ID'),
			),
			'is_finished' => array(
				'title' => $this->l('Finished ?'),
				'width' => 'auto',
				'icon' => array(
					0 => 'disabled.gif',
					1 => 'enabled.gif',
					'default' => 'disabled.gif',
				),
				'search' => false,
			),
			'message' => array(
				'title' => $this->l('Message'),
				'orderby' => false
			),
			'date' => array(
				'title' => $this->l('Date'),
				'type' => 'datetime',
				'orderby' => true
			),
			'is_processing' => array(
				'title' => $this->l('Delete ?'),
				'callback' => 'getDelete',
				'align' => 'center',
				'search' => false,
			),
		);

		$this->identifier = 'lengow_order_id';

		parent::__construct();

	}


	/**
	* postProcess handle every checks before saving products information
	*
	* @param mixed $token
	* @return void
	*/
	public function postProcess($token = null)
	{
		if (Tools::getValue('delete') != '')
			LengowCore::deleteProcessOrder(Tools::getValue('delete'));
		if (Tools::getValue('delete'.$this->table))
			$this->processBulkDelete();
		parent::postProcess($token);
	}

	/**
	* Get delete link for log
	*
	* @return string Link
	*/
	public function getDelete($echo, $row)
	{
		$echo = $echo;
		$token = Tools::getAdminTokenLite('AdminLengowLog', Context::getContext());
		return '<a href="index.php?controller=AdminLengowLog&delete='.$row['lengow_order_id'].'&token='.$token.'"><img src="'._PS_ADMIN_IMG_.'delete.gif" /></a>';
	}

	/**
	* Delete selecteded logs to Lengow
	*/
	protected function processBulkDelete()
	{
		$logs = Tools::getValue($this->table.'Box');
		if (is_array($logs) && (count($logs)))
			foreach ($logs as $log)
				LengowCore::deleteProcessOrder($log);
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
		$cookie = Context::getContext()->cookie;
		$currentIndex = 'index.php?tab=AdminLengowLog14';
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
					<td '.(isset($params['position']) ? ' id="td_'.(isset($id_category) && $id_category ? $id_category : 0).'_'.$id.'"' : '').' class="'.((!isset($this->noLink) || !$this->noLink) ? 'pointer' : '').((isset($params['position']) && $this->_orderBy == 'position') ? ' dragHandle' : '').(isset($params['align']) ? ' '.$params['align'] : '').'" ';
					if (!isset($params['position']) && (!isset($this->noLink) || !$this->noLink))
						echo ' onclick="document.location = \''.$currentIndex.'&'.$this->identifier.'='.$id.($this->view ? '&view' : '&update').$this->table.'&token='.($token != null ? $token : $this->token).'\'">'.(isset($params['prefix']) ? $params['prefix'] : '');
					else
						echo '>';
					if ($key == 'is_processing')
					{
						$token = Tools::getAdminTokenLite('AdminLengowLog14');
						echo '<a href="index.php?tab=AdminLengowLog14&delete='.$id.'&token='.$token.'"><img src="'._PS_ADMIN_IMG_.'delete.gif" /></a>';
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
						if (isset($tr['id_image']))
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
		echo '<p><input type="submit" class="button" name="delete'.$this->table.'" value="'.$this->l('Delete').'" /></p>';
		echo '</td></tr></table>';
		echo '<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" /></form>';
		if (isset($this->_includeTab) && count($this->_includeTab))
			echo '<br /><br />';
	}

}
