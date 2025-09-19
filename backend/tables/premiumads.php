<?php
/**
 * @package		AdsManager
 * @subpackage	Tables
 * @copyright	Copyright (C) 2010-2014 Juloa.com
 * @license		GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.table');

class AdsmanagerTablePremiumad extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__adsmanager_premium_ads', 'id', $db);
    }

    /**
     * Overwrite the check method to add custom validation.
     */
    public function check()
    {
        if (empty($this->headline)) {
            $this->setError(JText::_('COM_ADSMANAGER_ERROR_HEADLINE_REQUIRED'));
            return false;
        }

        if (empty($this->url) && empty($this->custom_html)) {
            $this->setError(JText::_('COM_ADSMANAGER_ERROR_URL_OR_HTML_REQUIRED'));
            return false;
        }

        // priority default
        if (!isset($this->priority)) {
            $this->priority = 0;
        }

        return true;
    }
}
