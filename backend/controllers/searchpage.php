<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'tables');


/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class AdsmanagerControllerSearchpage extends TController
{
	function __construct($config= array()) {
		parent::__construct($config);
	}
	
	function init()
	{
		// Set the default view name from the Request
		$this->_view = $this->getView("admin",'html');

		// Push a model into the view
		$this->_model = $this->getModel( "searchpage");
		if (!JError::isError( $this->_model )) {
			$this->_view->setModel( $this->_model, true );
		}
		
		$fieldmodel = $this->getModel("field");
		$this->_view->setModel( $fieldmodel );
	}
	
	function display($cachable = false, $urlparams = false)
	{
		$this->init();
		$this->_view->setLayout("searchpage");
		$this->_view->display();
	}
	
	function save()
	{
		$this->canAccess();
		$app = JFactory::getApplication();
		
		$conf = JTable::getInstance('searchpage', 'AdsmanagerTable');
		// bind it to the table
		if (!$conf -> bind(JRequest::get( 'post',JREQUEST_ALLOWHTML  ))) {
			return JError::raiseWarning( 500, $conf->getError() );
		}
		// store it in the db
		if (!$conf -> store()) {
			return JError::raiseWarning( 500, $conf->getError() );
		}	
		
		cleanAdsManagerCache();
	
		$app->redirect( 'index.php?option=com_adsmanager&c=searchpage', JText::_('ADSMANAGER_SEARCH_PAGE_SAVED') ,'message');
	}

	private function canAccess() {
		//check if the user can access the search page
		$user = JFactory::getUser();
		if(!$user->authorise('adsmanager.accesssearchpage','com_adsmanager')) {
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		return true;
	}
}
