<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2025 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class AdsmanagerModelImportad extends TModel
{
    // Tu si môžeš ukladať alebo načítať dáta na import, alebo metódy na spracovanie JSON

    /**
     * Získa dáta importu zo session / user state
     */
    public function getImportData()
    {
        $app = JFactory::getApplication();
        return $app->getUserState('com_adsmanager.import_data');
    }

    /**
     * Uloží importované dáta do user state (session)
     * @param array $data
     */
    public function setImportData($data)
    {
        $app = JFactory::getApplication();
        $app->setUserState('com_adsmanager.import_data', $data);
    }

    /**
     * Metóda na načítanie a spracovanie JSON súboru
     * @param string $filepath
     * @return bool|array
     */
    public function loadJsonFile($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $content = file_get_contents($filepath);
        $json = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }
        return $json;
    }

    /**
     * Môžeš pridať ďalšie metódy pre vytvorenie draftu, validáciu, ukladanie do DB atď.
     */
    public function saveDraft($data)
    {
        $db = JFactory::getDbo();
        $ad = new stdClass();

        // Vyčistenie ceny
        if (isset($data['ad_price'])) {
            // odstráni všetko okrem číslic a bodky/čiarky
            $cleanPrice = preg_replace('/[^\d.,]/', '', $data['ad_price']);
            // nahradí čiarku bodkou (ak by niekto použil 2,5)
            $cleanPrice = str_replace(',', '.', $cleanPrice);
            // prekonvertuje na float
            $data['ad_price'] = (float) $cleanPrice;
        }

        $ad->category = !empty($data['category']) ? (int)$data['category'] : 0;
        $ad->userid = 2315;
        $ad->name = !empty($data['name']) ? $data['name'] : '';
        $ad->images = '';
        $ad->ad_zip = !empty($data['ad_zip']) ? $data['ad_zip'] : null;
        $ad->ad_city = !empty($data['ad_city']) ? $data['ad_city'] : '';
        $ad->ad_phone = !empty($data['ad_phone']) ? $data['ad_phone'] : '';
        $ad->email = !empty($data['email']) ? $data['email'] : '';
        $ad->ad_kindof = !empty($data['ad_kindof']) ? $data['ad_kindof'] : null;
        $ad->ad_headline = !empty($data['ad_headline']) ? $data['ad_headline'] : '';
        $ad->ad_text = !empty($data['ad_text']) ? $data['ad_text'] : '';
        $ad->ad_state = !empty($data['ad_state']) ? $data['ad_state'] : null;
        $ad->ad_price = !empty($data['ad_price']) ? $data['ad_price'] : '';
        $ad->ad_engine = !empty($data['ad_engine']) ? $data['ad_engine'] : '';
        $ad->ad_priceother = !empty($data['ad_priceother']) ? $data['ad_priceother'] : '';

        $now = JFactory::getDate()->toSql();

        // Výpočet expiračného dátumu (92 dní od publikovania)
        // Načítaj počet dní z konfigurácie
        $query = $db->getQuery(true)
            ->select($db->quoteName('ad_duration'))
            ->from($db->quoteName('#__adsmanager_config'))
            ->where('id = 1'); // alebo podľa potreby (ak máš len jeden riadok)
        $db->setQuery($query);
        $adDuration = (int) $db->loadResult();

        // Nastav expiráciu podľa hodnoty
        $newDate = JFactory::getDate()->modify('+' . $adDuration . ' days')->toSql();

        $ad->date_created = $now;
        $ad->date_modified = $now;
        $ad->date_recall = null;
        $ad->publication_date = $now;
        $ad->expiration_date = $expiration; // nový dátum
        $ad->recall_mail_sent = 0;
        $ad->views = 0;
        $ad->published = 0; // draft
        $ad->metadata_description = null;
        $ad->metadata_keywords = null;
        $ad->ad_expiration = 1; // nastavenie na 1

        // Vloženie inzerátu
        $result = $db->insertObject('#__adsmanager_ads', $ad);

        if (!$result) {
            throw new Exception($db->getErrorMsg());
        }

        // Získaj ID vloženého inzerátu
        $adid = (int) $db->insertid();

        if ($adid > 0) {
            // Vloženie do tabuľky adcat (prepojenie s kategóriou)
            $catid = 10;
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__adsmanager_adcat'))
                ->columns([$db->quoteName('adid'), $db->quoteName('catid')])
                ->values($db->quote($adid) . ',' . $db->quote($catid));
            $db->setQuery($query);
            $db->execute();
        } else {
            throw new Exception('Nepodarilo sa získať ID vloženého inzerátu.');
        }

        return $adid;
    }

    public function purgeExpired()
    {
        $db = JFactory::getDbo();

        // Vyber expirované a publikované inzeráty, ktoré ešte nie sú v archíve
        $query = $db->getQuery(true)
            ->select('a.id, a.email, a.ad_headline, ac.catid')
            ->from($db->quoteName('#__adsmanager_ads', 'a'))
            ->join('LEFT', $db->quoteName('#__adsmanager_adcat', 'ac') . ' ON a.id = ac.adid')
            ->where('a.ad_expiration = 1')
            ->where('a.published = 1') // iba publikované
            ->where('a.expiration_date < NOW()')
            ->where('ac.catid != 39'); // vynechaj už archivované
        $db->setQuery($query);
        $ads = $db->loadObjectList();

        if (!$ads) {
            return 0; // nič nenašlo
        }

        foreach ($ads as $ad) {
            // Ulož pôvodnú kategóriu (ak ešte nie je uložená)
            $updateQuery = $db->getQuery(true)
                ->update($db->quoteName('#__adsmanager_adcat'))
                ->set($db->quoteName('old_catid') . ' = ' . (int)$ad->catid)
                ->set($db->quoteName('catid') . ' = 39')
                ->where($db->quoteName('adid') . ' = ' . (int)$ad->id);
            $db->setQuery($updateQuery);
            $db->execute();

            // Pošli email používateľovi
            $this->sendExpirationEmail($ad);
        }

        return count($ads); // počet spracovaných inzerátov
    }

    /**
     * Sends an expiration notification email to the ad owner
     */
    private function sendExpirationEmail($ad)
    {
        if (empty($ad->email)) {
            return false;
        }

        // Priamy link na obnovenie
        $restoreLink = JURI::root() . 'index.php?option=com_adsmanager&c=restoread&task=restoread&id=' . (int)$ad->id;

        $name = htmlspecialchars($ad->name, ENT_QUOTES, 'UTF-8');

        $subject = "RALLYBAZAR – Váš inzerát vypršal / Your ad has expired";
        $body = "
            Dobrý deň {$name},<br>
            Váš inzerát <strong>{$ad->ad_headline}</strong> vypršal a bol presunutý do archívu.<br><br>
            Ak si želáte inzerát obnoviť, kliknite na tento odkaz:<br>
            <a href='{$restoreLink}'>Obnoviť inzerát</a><br><br>
            Ďakujeme,<br>
            " . JFactory::getConfig()->get('sitename') . " Admin<br><br>
            <hr>
            Hello {$name},<br>
            Your ad <strong>{$ad->ad_headline}</strong> has expired and was moved to the archive.<br><br>
            If you wish to restore it, please click the link below:<br>
            <a href='{$restoreLink}'>Restore your ad</a><br><br>
            Thank you,<br>
            " . JFactory::getConfig()->get('sitename') . " Admin
        ";

        return $this->sendEmail($ad->email, $subject, $body);
    }

    private function sendEmail($email, $subject, $body)
    {
        $mailer = JFactory::getMailer();
        $config = JFactory::getConfig();

        $sender = array(
            $config->get('mailfrom'),
            $config->get('fromname')
        );

        $mailer->setSender($sender);
        $mailer->addRecipient($email);
        $mailer->setSubject($subject);
        $mailer->setBody($body);
        $mailer->isHtml(true);

        return $mailer->Send();
    }

}
