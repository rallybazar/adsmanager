<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// no direct access
defined('_JEXEC') or die( 'Restricted access' );

require_once(JPATH_ROOT."/components/com_adsmanager/lib/core.php");

require_once(JPATH_BASE.'/administrator/components/com_adsmanager/models/configuration.php');
require_once(JPATH_BASE.'/administrator/components/com_adsmanager/models/field.php');
require_once(JPATH_BASE.'/administrator/components/com_adsmanager/models/category.php');
require_once(JPATH_BASE."/components/com_adsmanager/helpers/field.php");

/****************************************************/
jimport( 'joomla.session.session' );	
$currentSession = JSession::getInstance('none',array());
$defaultvalues = $currentSession->get("globalfilter_values",array());
			
$app = JFactory::getApplication();

$config = TConf::getConfig();
if (@$config->globalfilter_fieldname != "") {
	$db = JFactory::getDbo();
	$fieldname = $db->Quote($config->globalfilter_fieldname);
} else {
	echo "Error: no global filter set in adsmanager configuration";exit();
}

$fieldmodel  = new AdsmanagerModelField();
$field_values = array();
$field = null;
$searchfields = $fieldmodel->getFieldsByName($fieldname);
$field_values = $fieldmodel->getFieldValues();

foreach($searchfields as $f)
{
	if ($f->cbfieldvalues != "-1")
	{
		/*get CB value fields */
		$cbfieldvalues = $fieldmodel->getCBFieldValues($f->cbfieldvalues);
		$field_values[$f->fieldid] = $cbfieldvalues;
	}
	//only one field in searchfields;
	$field = $f;
	break;
}

$confmodel = new AdsmanagerModelConfiguration();
$conf = $confmodel->getConfiguration();

$baseurl = JURI::base();
$catid = JRequest::getInt('catid',0);
if ($catid != 0) {
	$urloptions = ($catid != 0) ? "&catid=".$catid:'';
} else {
	$urloptions = "";
}
$link = TRoute::_("index.php?option=com_adsmanager&view=list$urloptions");

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$fhelpler = new JHTMLAdsmanagerField($conf,$field_values,"2",$fieldmodel->getPlugins());//0 =>list
require(JModuleHelper::getLayoutPath('mod_adsmanager_global_filter',$params->get( 'layout','default')));
$content="";
$path = JPATH_ADMINISTRATOR.'/../libraries/joomla/database/table';
JTable::addIncludePath($path);