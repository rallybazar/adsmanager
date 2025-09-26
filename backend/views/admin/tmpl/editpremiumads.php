<?php
defined('_JEXEC') or die;

$ad = isset($this->ad) ? $this->ad : null;
?>

<div class="container">
    <h2><?php echo JText::_('COM_ADSMANAGER_EDIT_PREMIUM_AD'); ?></h2>

    <form action="<?php echo JRoute::_('index.php?option=com_adsmanager&task=premiumads.save'); ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="id" value="<?php echo isset($ad->id) ? (int)$ad->id : 0; ?>">

        <div class="form-group">
            <label><?php echo JText::_('Headline'); ?></label>
            <input type="text" name="headline" class="form-control" value="<?php echo isset($ad->headline) ? htmlspecialchars($ad->headline) : ''; ?>">
        </div>

        <div class="form-group">
            <label><?php echo JText::_('Description'); ?></label>
            <textarea name="description" class="form-control"><?php echo isset($ad->description) ? htmlspecialchars($ad->description) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label><?php echo JText::_('URL'); ?></label>
            <input type="text" name="url" class="form-control" value="<?php echo isset($ad->url) ? htmlspecialchars($ad->url) : ''; ?>">
        </div>

        <div class="form-group">
            <label><?php echo JText::_('Published'); ?></label>
            <select name="published" class="form-control">
                <option value="1" <?php echo (isset($ad->published) && $ad->published == 1) ? 'selected' : ''; ?>><?php echo JText::_('Yes'); ?></option>
                <option value="0" <?php echo (isset($ad->published) && $ad->published == 0) ? 'selected' : ''; ?>><?php echo JText::_('No'); ?></option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary"><?php echo JText::_('Save'); ?></button>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
