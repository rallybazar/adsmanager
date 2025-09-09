<?php
/**
 *  @package	PaidSystem
 *  @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 *  @license    GNU General Public License version 3, or later
 */
// no direct access
defined('_JEXEC') or die( 'Restricted access' );
	
require_once(JPATH_ROOT."/components/com_adsmanager/lib/core.php");

require_once(JPATH_BASE.'/administrator/components/com_adsmanager/models/configuration.php');
require_once(JPATH_BASE.'/administrator/components/com_adsmanager/models/content.php');
require_once(JPATH_BASE.'/administrator/components/com_adsmanager/models/field.php');
require_once(JPATH_BASE."/components/com_adsmanager/helpers/field.php");

/**
 * CB framework
 * @global CBframework $_CB_framework
 */
global $_CB_framework, $_CB_database, $ueConfig, $mainframe, $_SERVER;
if ( defined( 'JPATH_ADMINISTRATOR' ) ) {
	if ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) {
		echo 'CB not installed';
		return;
	}
	include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );
} else {
	if ( ! file_exists( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' ) ) {
		echo 'CB not installed';
		return;
	}
	include_once( $mainframe->getCfg( 'absolute_path' ) . '/administrator/components/com_comprofiler/plugin.foundation.php' );
}

cbimport( 'cb.field' );
cbimport( 'language.front' );

outputCbTemplate( $_CB_framework->getUi() );
$myId = $_CB_framework->myId();

$uri = JFactory::getURI();
$baseurl = JURI::base();
$contentid = JRequest::getInt('id',0);

	
loadAdsManagerCss();

$confmodel  = new AdsmanagerModelConfiguration();
$conf = $confmodel->getConfiguration();

$contentmodel  = new AdsmanagerModelContent();
$content = $contentmodel->getContent($contentid); //TODO get correct ad id

$fieldmodel  = new AdsmanagerModelField();
$fields = $fieldmodel->getFields();
$field_values = $fieldmodel->getFieldValues();

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

foreach($fields as $field)
{
	if ($field->cbfieldvalues != "-1")
	{
		/*get CB value fields */
		$cbfieldvalues = $fieldmodel->getCBFieldValues($field->cbfieldvalues);
		$field_values[$field->fieldid] = $cbfieldvalues;
	}
}
$plugins =$fieldmodel->getPlugins();
$fieldhelper = new JHTMLAdsmanagerField($conf,$field_values,"4",$plugins); 

if ($contentid !== 0) {
	$cbUser =& CBuser::getInstance($content->userid);
	$user =& $cbUser->getUserData();
	$thumbnailAvatarHtmlWithLink = $cbUser->getField( 'avatar', null, 'html', 'none', 'list' );
}

require(JModuleHelper::getLayoutPath('mod_adsmanager_contact','default'));


$content="";
$path = JPATH_ADMINISTRATOR.'/../libraries/joomla/database/table';
JTable::addIncludePath($path);

?>


