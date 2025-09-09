<?php

/**
 * @author 		Anthony Verdure 
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_adsmanager' . DS . 'tables');

/**
 * @package		Joomla
 * @subpackage	Contact
 */
class AdsmanagerModelEmailtemplate extends TModel
{
	function getEmailTemplate($id)
	{
		$this->_db->setQuery("SELECT * FROM #__adsmanager_emailtemplates WHERE id = " . (int)$id);
		$emailTemplate = $this->_db->loadObject();
		return $emailTemplate;
	}

	function isPublishedEmailTemplate($id)
	{
		$this->_db->setQuery("SELECT count(*) FROM #__adsmanager_emailtemplates WHERE id = " . (int)$id . " AND published = 1");
		$result = $this->_db->loadResult();
		if ($result > 0) {
			return true;
		} else {
			return false;
		}
	}

	function getEmailTemplates($strSearch, $strPublished, $limit, $limitstart)
	{
		$whereStrPublished = '';

		if ($strPublished == '1') {
			$whereStrPublished = ' AND published = 1 ';
		} elseif ($strPublished == '0') {
			$whereStrPublished = ' AND published = 0 ';
		}

		$sql = "SELECT *
				FROM `#__adsmanager_emailtemplates` 
				WHERE `subject` LIKE '%$strSearch%' 
				$whereStrPublished
				LIMIT $limit OFFSET $limitstart;";

		static $emailTemplates;
		if (!$emailTemplates) {
			$this->_db->setQuery($sql);
			$emailTemplates = $this->_db->loadObjectList();

			foreach ($emailTemplates as &$emailTemplate) {
				$emailTemplate->subject = JText::_($emailTemplate->subject);
			}
		}

		return $emailTemplates;
	}

	function getNbEmailTemplates($strSearch, $strPublished)
	{
		$whereStrPublished = '';

		if ($strPublished == '1') {
			$whereStrPublished = ' AND published = 1 ';
		} elseif ($strPublished == '0') {
			$whereStrPublished = ' AND published = 0 ';
		}

		$sql = "SELECT COUNT(*) 
				FROM `#__adsmanager_emailtemplates` 
				WHERE `subject` LIKE '%$strSearch%' 
				$whereStrPublished;";

		$this->_db->setQuery($sql);
		$result = $this->_db->loadResult();
		return $result;
	}
}