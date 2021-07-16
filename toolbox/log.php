<?php
/**
 * Copyright 2017 Lengow SAS.
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
 * @copyright 2017 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

require 'conf.inc.php';

$locale = new LengowTranslation();
$listFile = LengowToolbox::getData(LengowToolbox::DATA_TYPE_LOG);

require 'views/header.php';
?>
    <div class="container">
        <h1><?php echo $locale->t('toolbox.log.log_files'); ?></h1>
        <ul class="list-group">
            <?php
            foreach ($listFile as $file) {
                $name = $file[LengowLog::LOG_DATE]
                    ? date('l d F Y', strtotime($file[LengowLog::LOG_DATE]))
                    : $locale->t('toolbox.log.download_all');
                echo '<li class="list-group-item"><a href="' . $file[LengowLog::LOG_LINK] . '">' . $name . '</a></li>';
            }
            ?>
        </ul>
    </div><!-- /.container -->
<?php
require 'views/footer.php';
