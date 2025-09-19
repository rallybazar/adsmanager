<?php
defined('_JEXEC') or die('Restricted access');

$ad = $this->ad; // objekt z controlleru
?>

<form action="index.php?option=com_adsmanager&c=premiumads&task=save" method="post" name="adminForm" id="adminForm">
<table class="admintable">
<tr>
    <td class="key"><?php echo JText::_('Title'); ?></td>
    <td><input type="text" name="jform[ad_headline]" value="<?php echo htmlspecialchars($ad->ad_headline); ?>" size="50" /></td>
</tr>
<tr>
    <td class="key"><?php echo JText::_('Published'); ?></td>
    <td><?php echo JHTML::_('select.booleanlist', 'jform[published]', '', $ad->published); ?></td>
</tr>
<tr>
    <td class="key"><?php echo JText::_('Category'); ?></td>
    <td>
        <select name="jform[catid]">
        <?php foreach($this->cats as $cat) { ?>
            <option value="<?php echo $cat->id; ?>" <?php echo ($ad->catid == $cat->id) ? 'selected' : ''; ?>><?php echo $cat->name; ?></option>
        <?php } ?>
        </select>
    </td>
</tr>
<tr>
    <td class="key"><?php echo JText::_('User'); ?></td>
    <td><input type="text" name="jform[userid]" value="<?php echo htmlspecialchars($ad->userid); ?>" size="20" /></td>
</tr>
<tr>
    <td class="key"><?php echo JText::_('Date Created'); ?></td>
    <td><?php echo $ad->date_created; ?></td>
</tr>
</table>

<input type="hidden" name="jform[id]" value="<?php echo $ad->id; ?>" />
<input type="hidden" name="option" value="com_adsmanager" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="c" value="premiumads" />
<?php echo JHTML::_('form.token'); ?>
<div>
    <button type="submit" class="btn btn-primary"><?php echo JText::_('Save'); ?></button>
    <button type="button" onclick="Joomla.submitbutton('cancel');" class="btn"><?php echo JText::_('Cancel'); ?></button>
</div>
</form>
