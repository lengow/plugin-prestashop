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
 * The Lengow Option Class.
 * User to generate option list on configuration.
 *
 */
class LengowOption
{

    /**
     * tracker ID.
     */
    public $id;

    /**
     * tracker name.
     */
    public $name;

    /**
     * Make a new tracker option.
     *
     * @param integer $id The tracker type unique ID.
     * @param varchar $token The tracker type name.
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}