<?php

/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
//include (JPATH_ROOT.'/libraries/joomla/html/html/access.php');



?>
<?php
$script = '
	Joomla.submitbutton = function(pressbutton) {
    ';

$editor = JEditor::getInstance();

$script .= '
        Joomla.submitform(pressbutton);
	}';

JFactory::getApplication()->getDocument()->addScriptDeclaration($script);
?>
<?php JText::_('ADSMANAGER_EMAIL_TEMPLATE_EDITION'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
    <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">

        <tr>
            <td><?php echo JText::_('ADSMANAGER_TH_EMAILS_FIELD_KEY'); ?></td>
            <td colspan="2"><input type="text" size="50" name="key" value="<?php echo @$this->row->key; ?>" /></td>
        </tr>
        <tr>
            <td><?php echo JText::_('ADSMANAGER_TH_EMAILS_FIELD_LANGUAGE'); ?></td>
            <td colspan="2">
                    <?php echo AdsmanagerHelperSelect::languages(@$this->row->language); ?>
            </td>
        </tr>

        <tr>
            <td><?php echo JText::_('ADSMANAGER_TH_PUBLISH'); ?></td>
            <td colspan="2">
                <select name="emailTemplatePublished" id="emailTemplatePublished">
                    <option value="1" <?php if (@$this->row->published == 1) {
                                            echo "selected";
                                        } ?>><?php echo JText::_('ADSMANAGER_PUBLISH'); ?></option>
                    <option value="0" <?php if (@$this->row->published == 0) {
                                            echo "selected";
                                        } ?>><?php echo JText::_('ADSMANAGER_NO_PUBLISH') ?></option>
                </select>
            </td>
        </tr>

        <?php if ($this->config->metadata_mode != 'nometadata') : ?>
            <tr>
                <td><?php echo JText::_('ADSMANAGER_TH_EMAILS_FIELD_SUBJECT'); ?></td>
                <td colspan="2"><input type="text" size="50" name="subject" value="<?php echo @$this->row->subject; ?>" /></td>

            </tr>
            <tr>
                <td><?php echo JText::_('ADSMANAGER_TH_EMAILS_FIELD_BODY'); ?></td>
                <td colspan="2">
                    <?php
                    $editor = JFactory::getEditor(); 
                    echo $editor->display('body', @$this->row->body, '100%', '350', 75, 20);
                    ?>
                </td>
            </tr>
        <?php endif ?>

    </table>
    <input type="hidden" name="id" value="<?php echo @$this->row->id; ?>" />
    <input type="hidden" name="option" value="com_adsmanager" />
    <input type="hidden" name="c" value="emailtemplates" />
    <input type="hidden" name="task" value="" />
</form> 
