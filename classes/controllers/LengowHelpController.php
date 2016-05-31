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

class LengowHelpController extends LengowController
{

    /**
     * Process Post Parameters
     */
    public function postProcess()
    {
    }

    public function display()
    {
        $lengowLink = new LengowLink();
        $this->context->smarty->assign('mailto', $this->getMailTo());
        $this->context->smarty->assign('lengow_ajax_link', $lengowLink->getAbsoluteAdminLink('AdminLengowHelp', true));
        parent::display();
    }

    /**
     * Generate mailto for help page
     */
    public function getMailTo()
    {
        $mailto = LengowSync::getSyncData();
        $mail = 'support.lengow.zendesk@lengow.com';
        $subject = $this->locale->t('help.screen.mailto_subject');
        $result = LengowConnector::queryApi('get', '/v3.0/cms');
        $body = '%0D%0A%0D%0A%0D%0A%0D%0A%0D%0A'
            . $this->locale->t('help.screen.mail_lengow_support_title').'%0D%0A';
        if (isset($result->cms)) {
            $body .= 'commun_account : '.$result->cms->common_account.'%0D%0A';
        }
        foreach ($mailto as $key => $value) {
            if ($key == 'domain_name' || $key == 'token' || $key == 'return_url' || $key == 'shops') {
                continue;
            }
            $body .= $key.' : '.$value.'%0D%0A';
        }
        $shops = $mailto['shops'];
        $i = 1;
        foreach ($shops as $shop) {
            foreach ($shop as $item => $value) {
                if ($item == 'name') {
                    $body .= 'Store '.$i.' : '.$value.'%0D%0A';
                } elseif ($item == 'feed_url') {
                    $body .= $value . '%0D%0A';
                }
            }
            $i++;
        }
        $html = '<a href="mailto:'. $mail;
        $html.= '?subject='. $subject;
        $html.= '&body='. $body .'" ';
        $html.= 'title="'. $this->locale->t('help.screen.need_some_help').'" target="_blank">';
        $html.=  $this->locale->t('help.screen.mail_lengow_support');
        $html.= '</a>';
        return $html;
    }
}
