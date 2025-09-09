<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

JHtml::_('behavior.tooltip');

$user = JFactory::getUser();

?>
<style>
	#adsmanager-dashboard .row-fluid {
		margin-bottom: 10px;
	}
	#adsmanager-dashboard a {
		-webkit-border-radius: 10px;
		-moz-border-radius: 10px;
		border-radius: 10px;
		border: 1px solid #DDD;
		padding: 5px 10px;
		width: 136px;
		display: block;
		text-align: center;
		text-decoration: none;
		color: #000;
	}
	
	#adsmanager-dashboard a:hover {
		background-color: #ECECEC;
	}
</style>
<div id="adsmanager-dashboard">
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span12">
				<h1><?php echo JText::_('COM_ADSMANAGER_DASHBOARD_TITLE'); ?></h1>
			</div>
		</div>
		<div class="row-fluid">
            <?php if($user->authorise('adsmanager.accessconfiguration','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=configuration"><?php echo JText::_('COM_ADSMANAGER_CONFIGURATION'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accessfield','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=fields"><?php echo JText::_('COM_ADSMANAGER_FIELDS'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accesscategory','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=categories"><?php echo JText::_('COM_ADSMANAGER_CATEGORIES'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accesscontent','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=contents"><?php echo JText::_('COM_ADSMANAGER_CONTENTS'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accesslayoutcontentform','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=contentform"><?php echo JText::_('COM_ADSMANAGER_CONTENT_FORM'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accesslayoutlist','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=columns"><?php echo JText::_('COM_ADSMANAGER_COLUMNS'); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <div class="row-fluid">
            <?php if($user->authorise('adsmanager.accesspositiondetails','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=positions"><?php echo JText::_('COM_ADSMANAGER_AD_DISPLAY'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accesssearchmodule','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=searchmodule"><?php echo JText::_('COM_ADSMANAGER_SEARCH_MODULE'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accessfieldimage','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=fieldimages"><?php echo JText::_('COM_ADSMANAGER_FIELD_IMAGES'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accessmail','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=mails"><?php echo JText::_('COM_ADSMANAGER_MAILS'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accessplugin','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=plugins"><?php echo JText::_('COM_ADSMANAGER_PLUGINS'); ?></a>
                </div>
            <?php endif; ?>
            <?php if($user->authorise('adsmanager.accessimport','com_adsmanager')) : ?>
                <div class="span2">
                    <a href="index.php?option=com_adsmanager&amp;c=importad"><?php echo JText::_('COM_ADSMANAGER_IMPORTADS'); ?></a>
                </div>
            <?php endif; ?>
		</div>
	</div>
</div>