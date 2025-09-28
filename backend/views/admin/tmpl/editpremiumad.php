<?php
defined('_JEXEC') or die;

// Joomla behaviors
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.calendar');

// Toolbar
JToolBarHelper::title(JText::_('COM_ADSMANAGER_EDIT_PREMIUM_AD'), 'generic.png');
JToolBarHelper::save('save');    // task save
JToolBarHelper::apply('apply');  // task apply
JToolBarHelper::cancel('cancel');

$ad = isset($this->content) ? $this->content : null;
?>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm" enctype="multipart/form-data">

<table class="adminform">

<!-- User -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_TH_USER'); ?></td>
    <td>
        <select name="userid" id="userid" class="required">
            <option value=""><?php echo JText::_('COM_ADSMANAGER_SELECT_USER');?></option>
            <?php foreach($this->users as $user): ?>
                <option value="<?php echo $user->id;?>" <?php echo ($user->id == @$ad->userid) ? "selected" : ""; ?>>
                    <?php echo $user->name.' ('.$user->username.')'; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>

<!-- Headline -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_HEADLINE'); ?></td>
    <td>
        <input type="text" name="headline" value="<?php echo htmlspecialchars(@$ad->headline); ?>" size="60" maxlength="255" class="required" />
    </td>
</tr>

<!-- Description -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_DESCRIPTION'); ?></td>
    <td>
        <textarea name="description" rows="5" cols="60"><?php echo htmlspecialchars(@$ad->description); ?></textarea>
    </td>
</tr>

<!-- Image URL -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_IMAGE_URL'); ?></td>
    <td>
        <input type="url" name="image" value="<?php echo htmlspecialchars(@$ad->image); ?>" size="60" class="required" />
        <br>
        <?php if (!empty($ad->image)): ?>
            <img id="imagePreview" src="<?php echo htmlspecialchars($ad->image); ?>" 
                 style="max-width:200px; margin-top:5px;" />
        <?php else: ?>
            <img id="imagePreview" src="" style="display:none; max-width:200px; margin-top:5px;" />
        <?php endif; ?>
    </td>
</tr>

<script type="text/javascript">
document.querySelector('input[name="image"]').addEventListener('input', function(event) {
    var img = document.getElementById('imagePreview');
    if (this.value.trim() !== '') {
        img.src = this.value;
        img.style.display = 'block';
    } else {
        img.src = '';
        img.style.display = 'none';
    }
});
</script>

<!-- URL -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_URL'); ?></td>
    <td>
        <input type="url" name="url" value="<?php echo htmlspecialchars(@$ad->url); ?>" size="60" class="required" />
    </td>
</tr>

<!-- Custom HTML -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_CUSTOM_HTML'); ?></td>
    <td>
        <textarea name="custom_html" rows="5" cols="60"><?php echo htmlspecialchars(@$ad->custom_html); ?></textarea>
    </td>
</tr>

<!-- Priority -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_PRIORITY'); ?></td>
    <td>
        <input type="number" name="priority" value="<?php echo (int)@$ad->priority; ?>" min="0" />
    </td>
</tr>

<!-- Active From -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_ACTIVE_FROM'); ?></td>
    <td>
        <?php echo JHtml::_('calendar', @$ad->active_from, 'active_from', 'active_from', '%Y-%m-%d %H:%M:%S', array('class'=>'required')); ?>
    </td>
</tr>

<!-- Active To -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_ACTIVE_TO'); ?></td>
    <td>
        <?php echo JHtml::_('calendar', @$ad->active_to, 'active_to', 'active_to', '%Y-%m-%d %H:%M:%S'); ?>
    </td>
</tr>

<!-- Published -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_PUBLISHED'); ?></td>
    <td>
        <select name="published">
            <option value="1" <?php echo (@$ad->published) ? "selected" : ""; ?>><?php echo JText::_('JPUBLISHED');?></option>
            <option value="0" <?php echo (!@$ad->published) ? "selected" : ""; ?>><?php echo JText::_('JUNPUBLISHED');?></option>
        </select>
    </td>
</tr>

<!-- Views -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_VIEWS'); ?></td>
    <td>
        <input type="number" name="views" value="<?php echo (int)@$ad->views; ?>" readonly />
    </td>
</tr>

<!-- Date Created -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_DATE_CREATED'); ?></td>
    <td>
        <?php echo JHtml::_('calendar', @$ad->date_created, 'date_created', 'date_created', '%Y-%m-%d %H:%M:%S'); ?>
    </td>
</tr>

<!-- Date Modified -->
<tr>
    <td><?php echo JText::_('COM_ADSMANAGER_DATE_MODIFIED'); ?></td>
    <td>
        <input type="text" name="date_modified" value="<?php echo @$ad->date_modified; ?>" readonly />
    </td>
</tr>

</table>

<input type="hidden" name="id" value="<?php echo @$ad->id; ?>" />
<input type="hidden" name="option" value="com_adsmanager" />
<input type="hidden" name="c" value="premiumads" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>

</form>

<script type="text/javascript">
document.getElementById('imageInput').addEventListener('change', function(event) {
    var input = event.target;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('imagePreview');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
});
</script>
