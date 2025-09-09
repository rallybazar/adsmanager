<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2015 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'tables');
jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class AdsmanagerControllerDashboard extends TController
{
	var $_view = null;
	var $_model = null;
	
	function __construct($config= array()) {
		parent::__construct($config);
	}
	
	function init()
	{
		// Set the default view name from the Request
		$this->_view = $this->getView("admin",'html');
		
		// Push a model into the view
		$this->_model = $this->getModel("dashboard");
		if (!JError::isError( $this->_model )) {
			$this->_view->setModel( $this->_model, true );
		}
	    
		$confmodel	  = $this->getModel("configuration");
		$this->_view->setModel( $confmodel );
	}
	
	function display($cachable = false, $urlparams = false)
	{
		$this->init();
		$this->_view->setLayout("dashboard");
		$this->_view->display();
	}
	
}