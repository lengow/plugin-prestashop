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
?>

<h1>Logs</h1>

<ul class="list-group">
<?php
foreach ($listFile as $file) {
    echo '<li class="list-group-item">';
    echo '<a href="/modules/lengow/toolbox/log.php?action=download&file='.urlencode($file['short_path']).'">'
        .$file['name'].'</a>';
    echo '</li>';
}
echo '<li class="list-group-item">';
echo '<a href="/modules/lengow/toolbox/log.php?action=download_all">Tous les fichiers de logs</a>';
echo '</li>';
?>
</ul>