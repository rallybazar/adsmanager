<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (version_compare(JVERSION,'1.6','>=')) {
    //ACL
    if (!JFactory::getUser()->authorise('core.manage', 'com_adsmanager')) {
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }
}

// Make sure the user is authorised to view this page
$user = JFactory::getUser();

// Component Helper
jimport('joomla.application.component.helper');

require_once(JPATH_ROOT."/components/com_adsmanager/lib/core.php");

$controllerName = JRequest::getCmd( 'c', 'dashboard' );

require_once( JPATH_COMPONENT."/controllers/$controllerName.php" );
$controllerName = 'AdsmanagerController'.$controllerName;

$lang = JFactory::getLanguage();
$lang->load("com_adsmanager",JPATH_ROOT);

// Create the controller
$controller = new $controllerName();

if(version_compare(JVERSION,'1.6.0','>=')){
	JHtml::_('behavior.framework');
	if($user->authorise('adsmanager.accessconfiguration','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_CONFIGURATION'), 'index.php?option=com_adsmanager&amp;c=configuration');
	}
	if($user->authorise('adsmanager.accessfield','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_FIELDS'), 'index.php?option=com_adsmanager&amp;c=fields');
	}
	if($user->authorise('adsmanager.accesslayoutcontentform','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_CONTENT_FORM'), 'index.php?option=com_adsmanager&amp;c=contentform');
	}
	if($user->authorise('adsmanager.accesslayoutlist','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_COLUMNS'), 'index.php?option=com_adsmanager&amp;c=columns');
	}
	if($user->authorise('adsmanager.accesspositiondetails','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_AD_DISPLAY'), 'index.php?option=com_adsmanager&amp;c=positions');
	}
	if($user->authorise('adsmanager.accesscategory','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_CATEGORIES'), 'index.php?option=com_adsmanager&amp;c=categories');
	}
	if($user->authorise('adsmanager.accesscontent','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_CONTENTS'), 'index.php?option=com_adsmanager&amp;c=contents');
	}
	if($user->authorise('adsmanager.accessplugin','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_PLUGINS'), 'index.php?option=com_adsmanager&amp;c=plugins');
	}
	if($user->authorise('adsmanager.accessfieldimage','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_FIELD_IMAGES'), 'index.php?option=com_adsmanager&amp;c=fieldimages');
	}
	if($user->authorise('adsmanager.accesssearchmodule','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_SEARCH_MODULE'), 'index.php?option=com_adsmanager&amp;c=searchmodule');
	}
	if($user->authorise('adsmanager.accesssearchpage','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_SEARCH_PAGE'), 'index.php?option=com_adsmanager&amp;c=searchpage');
	}
	if($user->authorise('adsmanager.accessemailtemplate','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_EMAILTEMPLATES'), 'index.php?option=com_adsmanager&amp;c=emailtemplates');
	}
	if($user->authorise('adsmanager.accessmail','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_MAILS'), 'index.php?option=com_adsmanager&amp;c=mails');
	}
	if($user->authorise('adsmanager.accessexport','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_EXPORT'), 'index.php?option=com_adsmanager&amp;c=export');
	}
	if($user->authorise('adsmanager.accessimport','com_adsmanager')) {
		JSubMenuHelper::addEntry(JText::_('COM_ADSMANAGER_IMPORTADS'), 'index.php?option=com_adsmanager&amp;c=importad');
	}
}	

// Perform the Request task
$controller->execute(JRequest::getCmd('task', null));
$controller->redirect();

echo "<br/><div align='center'><i>Adsmanager 3.3.1</i></div>";
echo '<div class="alert">Upgrade to a PRO version, to get full features and support : <a href="http://Juloa.com/compare.html">Juloa.com</a></div>';
