<?php

/**
 * @author 		Anthony Verdure
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div>
		<div style="float:right">
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div style="clear:both"></div>
	</div>

	<input type="text" name="search" id="search" value="<?php echo $this->escape($this->strSearch); ?>" class="input-medium" onchange="document.adminForm.submit();" placeholder="<?php echo JText::_('ADSMANAGER_SEARCH_BUTTON') ?>" />

	<nobr>

		<button class="btn btn-mini" onclick="this.form.submit();">
			<?php echo JText::_('JSEARCH_FILTER'); ?>
		</button>
		<button class="btn btn-mini" onclick="document.adminForm.search.value='';this.form.submit();">
			<?php echo JText::_('JSEARCH_RESET'); ?>
		</button>

	</nobr>

	<div class="filter-select fltrt">
		<?php echo AdsmanagerHelperSelect::published('published', $this->published, array('onchange' => 'this.form.submit();', 'class' => 'input-medium')) ?>
	</div>

	<table class="adminlist table table-striped" id="itemsList">
		<thead>
			<tr>
				<th width="3%" class="hidden-phone"> <input type="checkbox" name="toggle" value="" onClick="Joomla.checkAll(this);" />
				<th width="2%" class="hidden-phone">
					<?php echo JHTML::_('grid.sort', JText::_('Id'), 'id', @$this->lists['order_Dir'], @$this->lists['order']); ?>
				</th>
				<th width="30%">
					<?php echo JHTML::_('grid.sort', JText::_('ADSMANAGER_TH_EMAIL_KEY'), 'key', @$this->lists['order_Dir'], @$this->lists['order']); ?>
				</th>
				<th width="30%">
					<?php echo JHTML::_('grid.sort', JText::_('ADSMANAGER_TH_EMAIL_LANGUAGE'), 'language', @$this->lists['order_Dir'], @$this->lists['order']); ?>
				</th>
				<th width="5%"><?php echo JText::_('ADSMANAGER_TH_SUBJECT_LINE'); ?></th>
				<th width="40%" class="text-center">
					<?php echo JHTML::_('grid.sort', 'Published', 'published', @$this->lists['order_Dir'], @$this->lists['order']); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php
			$num = 0;
			$orders = [];
			foreach ($this->list as $key => $emailTemplate) {
			?>
				<tr class="row<?php echo ($num & 1); ?>" item-id="<?php echo $emailTemplate->id; ?>">

					<td class="hidden-phone"><input type="checkbox" id="cb<?php echo $num; ?>" name="cid[]" value="<?php echo $emailTemplate->id; ?>" onclick="isChecked(this.checked);" /></td>

					<td class="hidden-phone"><?php echo $emailTemplate->id; ?></td>
					<td>
						<a href="<?php echo "index.php?option=com_adsmanager&c=emailtemplates&task=edit&id=" . $emailTemplate->id ?>"><?php echo $emailTemplate->key ?></a>
					</td>
					<td>
						<a href="<?php echo "index.php?option=com_adsmanager&c=emailtemplates&task=edit&id=" . $emailTemplate->id ?>"><?php echo $emailTemplate->language ?></a>
					</td>
					<td>
						<a href="<?php echo "index.php?option=com_adsmanager&c=emailtemplates&task=edit&id=" . $emailTemplate->id ?>"><?php echo $emailTemplate->subject ?></a>
					</td>

					<td align='center'><?php echo JHTML::_('grid.published', $emailTemplate, $num); ?></td>
				</tr>
			<?php
				$num++;
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="filter_order" id="filter_order" value="id" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="asc" />
	<input type="hidden" name="option" value="com_adsmanager" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="c" value="emailtemplates" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
