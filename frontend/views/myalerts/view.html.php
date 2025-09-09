<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

require_once(JPATH_ROOT."/components/com_adsmanager/helpers/field.php");
require_once(JPATH_ROOT."/components/com_adsmanager/helpers/general.php");

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class AdsmanagerViewMyalerts extends TView
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$user		= JFactory::getUser();
		$pathway	= $app->getPathway();
		$document	= JFactory::getDocument();
		
		if ($user->id == 0) {
			TTools::redirectToLogin("index.php?option=com_adsmanager&view=myalerts");
			return;  
	    }
		
		$fieldmodel	    = $this->getModel( "field" );
		$alertmodel	    = $this->getModel( "alert" );
		$catmodel	    = $this->getModel( "category" );

		$uri = JFactory::getURI();
		$this->requestURL = $uri->toString();
		
		jimport( 'joomla.session.session' );	
		$conf = TConf::getConfig();
				
		$limitstart = JRequest::getInt("limitstart",0);	
		$limit = $app->getUserStateFromRequest('com_adsmanager.front_ads_per_page','limit',$conf->ads_per_page, 'int');
		
		$filters = array();
		$filters['userid'] = JFactory::getUser()->id;
		
        $total = $alertmodel->getNbAlerts($filters);
		$contents = $alertmodel->getAlerts($filters,$limitstart, $limit);
		
		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		$this->assignRef('pagination',$pagination);
		
		$this->assignRef('contents',$contents);
		
		$this->assignRef('conf',$conf);
		
		$cats = $catmodel->getCategoriesById();
		$this->assignRef('categories',$cats);
		
		$fields = $fieldmodel->getFields();
		$this->assignRef('fields',$fields);
			
		$field_values = $fieldmodel->getFieldValues();
		
		$plugins = $fieldmodel->getPlugins();
		$field = new JHTMLAdsmanagerField($conf,$field_values,'1',$plugins);
		$this->assignRef('field',$field);
				
		$general = new JHTMLAdsmanagerGeneral(0,$conf,$user);
		$this->assignRef('general',$general);
		
		parent::display($tpl);
	}
    
	function reorderDate( $date ){
		$format = JText::_('ADSMANAGER_DATE_FORMAT_LC');
		
		if ($date && (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/",$date,$regs))) {
			$date = mktime( 0, 0, 0, $regs[2], $regs[3], $regs[1] );
			$date = $date > -1 ? strftime( $format, $date) : '-';
		}
		return $date;
	}
}
