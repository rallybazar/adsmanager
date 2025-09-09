<?php
/**
 * @package Adsmanager
 * @subpackage AdsmanagerIconModule
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license GNU General Public License version 3, or later
 * @since 2.2
 * @version 1.0
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

// Load the language files
$lang = JFactory::getLanguage();

$links = array();

if(file_exists(JPATH_ADMINISTRATOR.'/components/com_adsmanager')) {
	require_once(JPATH_ROOT.'/components/com_adsmanager/lib/core.php');
}

if(file_exists(JPATH_ADMINISTRATOR.'/components/com_adsmanager')) {
	$adsmanager_configuration_enabled = (int) $params->get('adsmanager_configuration_enabled', 0);
	$adsmanager_fields_enabled = (int) $params->get('adsmanager_fields_enabled', 0);
	$adsmanager_columns_enabled = (int) $params->get('adsmanager_columns_enabled', 0);
	$adsmanager_positions_enabled = (int) $params->get('adsmanager_positions_enabled', 0);
	$adsmanager_categories_enabled = (int) $params->get('adsmanager_categories_enabled', 0);
	$adsmanager_contents_enabled = (int) $params->get('adsmanager_contents_enabled', 1);
	
	
	
	if ( $adsmanager_configuration_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_adsmanager&amp;c=configuration');
		$link->text = JText::_('COM_ADSMANAGER_CONFIGURATION');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_adsmanager/images/menu/adsconfig_30.png';
        } else {
            $link->img = '../components/com_adsmanager/images/menu/adsconfig_25.png';
        }
		$links[] = $link;
	}
	if ( $adsmanager_fields_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_adsmanager&amp;c=fields');
		$link->text = JText::_('COM_ADSMANAGER_FIELDS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_adsmanager/images/menu/adsfield_30.png';
        } else {
            $link->img = '../components/com_adsmanager/images/menu/adsfield_25.png';
        }
		$links[] = $link;
	}
	if ( $adsmanager_columns_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_adsmanager&amp;c=columns');
		$link->text = JText::_('COM_ADSMANAGER_COLUMNS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_adsmanager/images/menu/adscolumns_30.png';
        } else {
            $link->img = '../components/com_adsmanager/images/menu/adscolumns_25.png';
        }
		$links[] = $link;
	}
	if ( $adsmanager_positions_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_adsmanager&amp;c=positions');
		$link->text = JText::_('COM_ADSMANAGER_AD_DISPLAY');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_adsmanager/images/menu/adspositions_30.png';
        } else {
            $link->img = '../components/com_adsmanager/images/menu/adspositions_25.png';
        }
		$links[] = $link;
	}
	if ( $adsmanager_categories_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_adsmanager&amp;c=categories');
		$link->text = JText::_('COM_ADSMANAGER_CATEGORIES');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_adsmanager/images/menu/adscategory_30.png';
        } else {
            $link->img = '../components/com_adsmanager/images/menu/adscategory_25.png';
        }
		$links[] = $link;
	}
	if ( $adsmanager_contents_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_adsmanager&amp;c=contents');
		$link->text = JText::_('COM_ADSMANAGER_CONTENTS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_adsmanager/images/menu/adscontent_30.png';
        } else {
            $link->img = '../components/com_adsmanager/images/menu/adscontent_25.png';
        }
		$links[] = $link;
	}
}

if(file_exists(JPATH_ADMINISTRATOR.'/components/com_paidsystem')) {
	
	$paidsystem_configuration_enabled = (int) $params->get('paidsystem_configuration_enabled', 1);
	$paidsystem_categories_enabled = (int) $params->get('paidsystem_categories_enabled', 0);
	$paidsystem_fields_enabled = (int) $params->get('paidsystem_fields_enabled', 0);
	$paidsystem_durations_enabled = (int) $params->get('paidsystem_durations_enabled', 1);
	$paidsystem_tops_enabled = (int) $params->get('paidsystem_tops_enabled', 1);
	$paidsystem_highlights_enabled = (int) $params->get('paidsystem_highlights_enabled', 1);
	$paidsystem_featureds_enabled = (int) $params->get('paidsystem_featureds_enabled', 1);
	
	if ( $paidsystem_configuration_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=configuration');
		$link->text = JText::_('COM_PAIDSYSTEM_CONFIGURATION');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/paidconfig_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/paidconfig_25.png';
        }
		$links[] = $link;
	}
	if ( $paidsystem_categories_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=categories');
		$link->text = JText::_('COM_PAIDSYSTEM_CATEGORIES');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/paidcategory_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/paidcategory_25.png';
        }
		$links[] = $link;
	}
	if ( $paidsystem_fields_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=fields');
		$link->text = JText::_('COM_PAIDSYSTEM_FIELDS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/paidfield_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/paidfield_25.png';
        }
		$links[] = $link;
	}
	if ( $paidsystem_durations_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=durations');
		$link->text = JText::_('COM_PAIDSYSTEM_DURATIONS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/paidduration_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/paidduration_25.png';
        }
		$links[] = $link;
	}
	if ( $paidsystem_tops_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=tops');
		$link->text = JText::_('COM_PAIDSYSTEM_TOPS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/top_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/top_25.png';
        }
		$links[] = $link;
	}
	if ( $paidsystem_highlights_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=highlights');
		$link->text = JText::_('COM_PAIDSYSTEM_HIGHLIGHTS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/highlight_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/highlight_25.png';
        }
		$links[] = $link;
	}
	if ( $paidsystem_featureds_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_paidsystem&amp;c=featureds');
		$link->text = JText::_('COM_PAIDSYSTEM_FEATUREDS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../components/com_paidsystem/images/menu/featured_30.png';
        } else {
            $link->img = '../components/com_paidsystem/images/menu/featured_25.png';
        }
		$links[] = $link;
	}
}

if(file_exists(JPATH_ADMINISTRATOR.'/components/com_invoicing')) {
	$lang->load("com_invoicing");
	$invoicing_cpanel_enabled = (int) $params->get('invoicing_cpanel_enabled', 1);
	$invoicing_invoices_enabled = (int) $params->get('invoicing_invoices_enabled', 1);
	$invoicing_quotes_enabled = (int) $params->get('invoicing_quotes_enabled', 0);
	$invoicing_coupons_enabled = (int) $params->get('invoicing_coupons_enabled', 1);
	
	if ( $invoicing_cpanel_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_invoicing');
		$link->text = JText::_('COM_INVOICING_STATISTICS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../media/com_invoicing/images/menu/invoicestat_30.png';
        } else {
            $link->img = '../media/com_invoicing/images/menu/invoicestat_25.png';
        }
		$links[] = $link;
	}
	if ( $invoicing_invoices_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_invoicing&amp;view=invoices');
		$link->text = JText::_('COM_INVOICING_TITLE_INVOICES');
		if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../media/com_invoicing/images/menu/logo_30.png';
        } else {
            $link->img = '../media/com_invoicing/images/menu/logo_25.png';
        }
		$links[] = $link;
	}
	if ( $invoicing_quotes_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_invoicing&amp;view=quotes');
		$link->text = JText::_('COM_INVOICING_TITLE_QUOTES');
		if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../media/com_invoicing/images/menu/invoicequote_30.png';
        } else {
            $link->img = '../media/com_invoicing/images/menu/invoicequote_25.png';
        }
		$links[] = $link;
	}
	if ( $invoicing_coupons_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_invoicing&amp;view=coupons');
		$link->text = JText::_('COM_INVOICING_TITLE_COUPONS');
		if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../media/com_invoicing/images/menu/invoicecoupon_30.png';
        } else {
            $link->img = '../media/com_invoicing/images/menu/invoicecoupon_25.png';
        }
		$links[] = $link;
	}
}


if(file_exists(JPATH_ADMINISTRATOR.'/components/com_virtualmoney')) {
	$lang->load("com_virtualmoney");
	$virtualmoney_credits_enabled = (int) $params->get('virtualmoney_credits_enabled', 1);
	$virtualmoney_packs_enabled = (int) $params->get('virtualmoney_packs_enabled', 0);
	
	if ( $virtualmoney_credits_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_virtualmoney&amp;view=credits');
		$link->text = JText::_('COM_VIRTUALMONEY_TITLE_CREDITS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../media/com_virtualmoney/images/menu/vmcredit_30.png';
        } else {
            $link->img = '../media/com_virtualmoney/images/menu/vmcredit_25.png';
        }
		$links[] = $link;
	}
	if ( $virtualmoney_packs_enabled ) {
		$link = new stdClass();
		$link->link = JRoute::_('index.php?option=com_virtualmoney&amp;view=packs');
		$link->text = JText::_('COM_VIRTUALMONEY_TITLE_PACKS');
        if (version_compare(JVERSION,'3.0.0','>=')) {
            $link->img = '../media/com_virtualmoney/images/menu/vmpack_30.png';
        } else {
            $link->img = '../media/com_virtualmoney/images/menu/vmpack_25.png';
        }
		$links[] = $link;
	}
}
if (version_compare(JVERSION,'3.1.50','>=')) {
	require(JModuleHelper::getLayoutPath('mod_adsmanager_quickicons','default32'));
} else if (version_compare(JVERSION,'3.0.0','>=')) {
	require(JModuleHelper::getLayoutPath('mod_adsmanager_quickicons','default3'));
} else {
	require(JModuleHelper::getLayoutPath('mod_adsmanager_quickicons','default'));
}
$content = "";
?>