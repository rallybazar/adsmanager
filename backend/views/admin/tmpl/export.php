<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

JHtml::_('behavior.tooltip');
?>

<table class="adminlist table table-striped" id="itemsList">
	<thead>
		<tr>
			<th><?php echo JText::_('ADSMANAGER_EXPORT_TITLE');?></th>
		</tr>
	</thead>
	
	<tbody>
		<tr>
			<td>
			
			
			<form id="form" name="form" method="post" action="<?php echo "index.php?option=com_adsmanager&c=export&task=exportcsv"?>">
				<div class="alignRight">
						<select name="field_catsid[]" mosReq="1" mosLabel="<?php echo htmlspecialchars(JText::_('ADSMANAGER_FORM_CATEGORY')) ?>" multiple='multiple' id="field_catsid[]" size="<?php echo $this->nbcats+2;?>">
							<?php
						
							$this->selectCategories(0,"",$this->cats,-1,-1,1,@$this->field->catsid);
							?>
						</select>
				</div>
				<table class="formatTblClass">
					<thead>
						<tr>	 		  
						  <th width="3%" class="hidden-phone"> <input type="checkbox" name="toggle" value="" onClick="Joomla.checkAll(this);"></th>
						  <th width="2%" class="hidden-phone">#</th>
						  <th>
							<?php echo JHTML::_('grid.sort',   JText::_('ADSMANAGER_TH_NAME'), 'f.name', @$this->lists['order_Dir'], @$this->lists['order'] ); ?>
						  </th>
						  <th>
							<?php echo JHTML::_('grid.sort',   JText::_('ADSMANAGER_TH_TITLE'), 'f.title', @$this->lists['order_Dir'], @$this->lists['order'] ); ?> 
						  </th>
						 </tr>
						 
					</thead>
					<?php
					
					$k = 0;
					$i=0;
					$n=count( $this->fields );
					foreach($this->fields as $field) {
						
					?>
					<tr class="<?php echo "row$k"; ?> dndlist-sortable" sortable-group-id="1" item-id="<?php echo $field->fieldid?>" parents level="1">
							

						<td class="hidden-phone"><input type="checkbox" id="cb<?php echo $i;?>" name="<?php echo $field->name; ?>"  value="<?php echo $field->name; ?>" onClick="isChecked(this.checked);" /></td>
						<td class="hidden-phone"><?php echo $field->fieldid?></td>

						<td> <a href="index.php?option=com_adsmanager&c=fields&task=edit&cid=<?php echo $field->fieldid; ?>">
						<?php echo $field->name; ?> </a> </td>
					    <?php $field->title = JText::_($field->title);?>
						<td><?php echo $field->title; ?></td>
				
					</tr>
					
									
>
						
					
					<?php $i++;$k = 1 - $k; } ?>
					
					<!--  <tr>
						<td><span><?php echo JText::_('ADSMANAGER_EXPORT_NUMBER');?></span></td>
						<td><input type="number" name="fn" id="fn" /></td>
					</tr> -->
					
				</table>
                <table>
                    <tr>
						<td><span><?php echo JText::_('ADSMANAGER_EXPORT_PUBLISHED');?></span></td>
						<td><label for="no">No</label> 
							<input type="radio" name="published" id="no" value="0" checked />

							<label for="yes">Yes</label> 
							<input type="radio" name="published" id="yes" value="1" />
						</td>
					</tr>
					<tr>
						<td><span><?php echo JText::_('ADSMANAGER_EXPORT_IMAGES');?></span></td>
						<td><label for="no">No</label> 
							<input type="radio" name="images" id="no" value="0" checked />

							<label for="yes">Yes</label> 
							<input type="radio" name="images" id="yes" value="1" />
						</td>
					</tr>
					<tr>
						<td><span><?php echo JText::_('ADSMANAGER_EXPORT_DATE_CREATED');?></span></td>
						<td>
						
							<?php
								echo JHTML::calendar(date("Y-m-d"),'dateCreated', 'date_created', '%Y-%m-%d',array('size'=>'8','maxlength'=>'10','class'=>' validate[\'required\']',));
							  ?>
						</td>
					</tr>					
					<tr>
						<td><span><?php echo JText::_('ADSMANAGER_EXPORT_DATE_ENDED');?></span></td>
						<td>
						
							<?php
								echo JHTML::calendar(date("Y-m-d"),'dateEnded', 'date_ended', '%Y-%m-%d',array('size'=>'8','maxlength'=>'10','class'=>' validate[\'required\']',));
							  ?>
						</td>
					</tr>
					<tr>
						<td>
							<div>
								<input type="submit"  value="<?php echo JText::_('ADSMANAGER_EXPORT_BUTTON');?>" />
							</div>
						</td>
					</tr>
                </table>
			</form>  
			
			
			
			
			
			
			</td>	
		</tr>
	</tbody>
</table>