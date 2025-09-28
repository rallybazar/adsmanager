<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.table');

class AdsmanagerTablePremiumads extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__adsmanager_premium_ads', 'id', $db);
    }

    /**
     * Rozšírené bindovanie dát z formulára
     *
     * @param array $data
     * @param array $files
     * @return bool
     */
    public function bindContent($data, $files = array())
    {
        // Bind základné polia
        if (!$this->bind($data)) {
            return false;
        }

        // Obrázky
        if (!empty($files['image']['tmp_name'])) {
            jimport('joomla.filesystem.file');
            $uploadDir = JPATH_ROOT . '/images/premiumads/';
            if (!JFolder::exists($uploadDir)) {
                JFolder::create($uploadDir);
            }
            $filename = JFile::makeSafe($files['image']['name']);
            $dest = $uploadDir . $filename;
            if (JFile::upload($files['image']['tmp_name'], $dest)) {
                $this->image = 'images/premiumads/' . $filename;
            }
        }

        // Nastav date_modified
        $this->date_modified = JFactory::getDate()->toSql();

        return true;
    }

    /**
     * Uloženie záznamu do DB
     *
     * @return bool
     */
    public function saveContent()
    {
        // Volanie pred uložením cez event pluginov, ak budeš mať
        JPluginHelper::importPlugin('adsmanager');
        $dispatcher = JEventDispatcher::getInstance();
        $dispatcher->trigger('onBeforePremiumAdSave', array(&$this));

        if (!$this->store()) {
            return false;
        }

        // Volanie po uložením cez pluginy
        $dispatcher->trigger('onAfterPremiumAdSave', array(&$this));

        return true;
    }
}
