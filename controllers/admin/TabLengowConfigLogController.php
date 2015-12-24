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

class AdminLengowConfigLog14 extends AdminTab {

	public function __construct()
	{

		parent::__construct();

		$this->lang = false;
		$this->context = Context::getContext();
		$this->lite_display = true;
		$this->lang = true;
		$this->explicitSelect = true;
		$this->list_no_link = true;


		$this->template = 'layout.tpl';
		$this->display = 'view';


	}

}
