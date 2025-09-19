<?php
defined('_JEXEC') or die('Restricted access');
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<?php if (!empty($this->premiumAds)) : ?>
<table class="adminlist table table-striped">
<thead>
<tr>
    <th width="3%"><input type="checkbox" name="toggle" value="" onClick="Joomla.checkAll(this);" /></th>
    <th width="5"><?php echo JHTML::_('grid.sort', 'ID', 'a.id', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
    <th width="20%"><?php echo JHTML::_('grid.sort', JText::_('Title'), 'a.ad_headline', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
    <th width="10%"><?php echo JHTML::_('grid.sort', JText::_('Published'), 'a.published', @$this->lists['order_Dir'], @$this->lists['order']); ?></th>
    <th width="15%">User</th>
    <th width="15%">Category</th>
    <th width="10%">Date Created</th>
</tr>
</thead>
<tbody>
<?php
$k = 0;
foreach ($this->premiumAds as $i => $ad) :
?>
<tr class="row<?php echo $k; ?>">
    <td><input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $ad->id; ?>" onclick="Joomla.isChecked(this.checked);" /></td>
    <td><?php echo $ad->id; ?></td>
    <td><a href="index.php?option=com_adsmanager&c=premiumads&task=edit&id=<?php echo $ad->id; ?>"><?php echo $ad->ad_headline; ?></a></td>
    <td><?php echo JHTML::_('grid.published', $ad, $i); ?></td>
    <td><?php echo $ad->username; ?></td>
    <td><?php echo $ad->cat; ?></td>
    <td><?php echo $ad->date_created; ?></td>
</tr>
<?php
$k = 1 - $k;
endforeach;
?>
</tbody>
<tfoot>
<tr>
    <td colspan="7"><?php echo $this->pagination->getListFooter(); ?></td>
</tr>
</tfoot>
</table>
<?php else : ?>
    <p><?php echo JText::_('COM_ADSMANAGER_NO_PREMIUM_ADS'); ?></p>
<?php endif; ?>

<input type="hidden" name="option" value="com_adsmanager" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="c" value="premiumads" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
