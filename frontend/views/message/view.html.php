<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

require_once(JPATH_BASE."/components/com_adsmanager/helpers/field.php");
require_once(JPATH_ROOT.'/components/com_adsmanager/lib/tpermissions.php');

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class AdsmanagerViewMessage extends TView
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		if(!TPermissions::checkRightContact()) {
			$app->redirect('index.php', JText::_('ADSMANAGER_CONTACT_NO_ACCESS_RIGHT'), 'error');
		}

		$user		= JFactory::getUser();
		$pathway	= $app->getPathway();
		$document	= JFactory::getDocument();
		$contentmodel	    =$this->getModel( "content" );

		$conf = TConf::getConfig();

		$this->assignRef('conf',$conf);	
		
		$this->assignRef('user',$user);
		
        $fieldname = JRequest::getString('fname','');
		$this->assignRef('fieldname',$fieldname);
        
		$contentid = JRequest::getInt( 'contentid',	0 );
		$content = $contentmodel->getContent($contentid);
		$this->assignRef('content',$content);
		
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('adsmanagercontent');
		
		$event = new stdClass();
		$results = $dispatcher->trigger('ADSonMessageAfterForm', array ($content));
		$event->onMessageAfterForm = trim(implode("\n", $results));
		$this->assignRef('event',$event);
		
		$document->setTitle( JText::_('ADSMANAGER_PAGE_TITLE_MESSAGE'));
		
		parent::display($tpl);
	}
}
