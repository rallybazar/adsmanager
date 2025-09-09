<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<div class="juloawrapper">
    <div class="container-fluid">
        <?php $link = TRoute::_("index.php?option=com_adsmanager&task=savealert"); ?>
        <form action="<?php echo $link; ?>" class="form-horizontal" id="advancedsearch-form" method="post">
            <fieldset>
                <legend>
                <?php echo JText::_('ADSMANAGER_ALERT'); ?>
                </legend>
                <div class="row-fluid">
                <?php if ($this->search_by_cat == 1){ ?>
                <div class="control-group">
                    <label class="control-label" for="catid"></label>
                    <div class="controls">
                        <?php
                            switch($this->conf->single_category_selection_type) {
                                default:
                                case 'normal':
                                    JHTMLAdsmanagerCategory::displayNormalCategories("search_catid",$this->cats,$this->catid,array('root_allowed' => false));break;
                                case 'color':
                                    JHTMLAdsmanagerCategory::displayColorCategories("search_catid",$this->cats,$this->catid,array('root_allowed' => false));break;
                                case 'combobox':
                                    JHTMLAdsmanagerCategory::displayComboboxCategories("search_catid",$this->cats,$this->catid,array('root_allowed' => false));break;
                                case 'cascade':
                                    $separator = "<br/>";
                                    JHTMLAdsmanagerCategory::displaySplitCategories("search_catid",$this->cats,$this->catid,array('separator'=>$separator,'root_allowed' => false));break;
                            }
                        ?>
                    </div>
                </div>
                <?php } ?>
                <?php 
                    foreach($this->simple_fields as $fsearch) {
                    	echo "<div class='row-fluid'>";
                        echo "<div id='searchfield_$fsearch->fieldid' class=\"span12\">";
                        echo "<div class=\"control-group\">";
                        echo "<label class=\"control-label\" for=\"{$fsearch->name}\">".JText::_($fsearch->title)."</label>";
                        echo "<div class=\"controls\">";
                        $this->field->showFieldSearch($fsearch,0,$this->defaultvalues,true);
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                }?>	
                <?php if(!empty($this->advanced_fields)){ ?>
                    <?php 
                    foreach($this->advanced_fields as $fsearch) {
                    	echo "<div class='row-fluid'>";
                        echo "<div id='searchfield_$fsearch->fieldid' class=\"span12\">";
                        echo "<div class=\"control-group\">";
                        echo "<label class=\"control-label\" for=\"{$fsearch->name}\">".JText::_($fsearch->title)."</label>";
                        echo "<div class=\"controls\">";
                        $this->field->showFieldSearch($fsearch,0,$this->defaultvalues,true);
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    } ?>
                <?php } ?>
                <div class="row-fluid">
                	<div class='span12'>
                		<div class='control-group'>
                			<div class='control-label'><?php echo JText::_('ADSMANAGER_RECURRENCE')?></div>
                			<div class='controls'>
                			<select name="recurrence">
                				<option value="oneveryad" <?php if(isset($this->alert->recurrence) && $this->alert->recurrence == "oneveryad") echo "selected"; ?>><?php echo JText::_('ADSMANAGER_RECURRENCE_ONEVERYAD')?></option>
                				<option value="everyhour" <?php if(isset($this->alert->recurrence) && $this->alert->recurrence == "everyhour") echo "selected"; ?>><?php echo JText::_('ADSMANAGER_RECURRENCE_EVERYHOUR')?></option>
                				<option value="every12hours" <?php if(isset($this->alert->recurrence) && $this->alert->recurrence == "every12hours") echo "selected"; ?>><?php echo JText::_('ADSMANAGER_RECURRENCE_EVERY12HOURS')?></option>
                				<option value="everyday" <?php if(isset($this->alert->recurrence) && $this->alert->recurrence == "everyday") echo "selected"; ?>><?php echo JText::_('ADSMANAGER_RECURRENCE_EVERYDAY')?></option>
                				<option value="everyweek" <?php if(isset($this->alert->recurrence) && $this->alert->recurrence == "everyweek") echo "selected"; ?>><?php echo JText::_('ADSMANAGER_RECURRENCE_EVERYWEEK')?></option>
                			</select>
                			</div>
                		</div>
                	</div>
                </div>
                <input type="submit" class="btn btn-primary" value="<?php echo JText::_('ADSMANAGER_FORM_SUBMIT_TEXT'); ?>"/>
                <script type="text/javascript">
                    function updateModFields() {
                        var form = document.advancedsearch-form;
                        catid = jQ('#advancedsearch-form #search_catid').val();
                        <?php
                        $fields = array_merge($this->simple_fields,$this->advanced_fields);
                        foreach($fields as $field)
                        { 	
                            if (strpos($field->catsid, ",-1,") === false)
                            {
                            ?>
                            var field_condition = "<?php echo $field->catsid;?>";
                            var test = field_condition.indexOf( ","+catid+",", 0 );
                            var divfield = document.getElementById('searchfield_<?php echo $field->fieldid;?>');
                            if (test != -1) {
                                jQ('#searchfield_<?php echo $field->fieldid;?>').show();
                }
                else {
                                jQ('#searchfield_<?php echo $field->fieldid;?>').hide();
            } 
                        <?php
        }
                }
                        ?>
            }
                    function checkdependency(child,parentname,parentvalues) {
                        //Simple checkbox
                        if (jQ('input[name="'+parentname+'"]').is(':checkbox')) {
                            //alert("test");
                            if (jQ('input[name="'+parentname+'"]').is(':checked')) {
                                jQ('#advancedsearch-form #f'+child).show();
                                jQ('#advancedsearch-form #searchfield_'+child).show();
                            }
                            else {
                                jQ('#advancedsearch-form #f'+child).hide();
                                jQ('#advancedsearch-form #searchfield_'+child).hide();

                                //cleanup child field 
                                if (jQ('#advancedsearch-form #f'+child).is(':checkbox') || jQ('#advancedsearch-form #f'+child).is(':radio')) {
                                    jQ('#advancedsearch-form #f'+child).attr('checked', false);
                                }
                                else {
                                    jQ('#advancedsearch-form #'+child).val('');
                                }
                            } 
                        }
                        //If checkboxes or radio buttons, special treatment
                        else if (jQ('input[name="'+parentname+'"]').is(':radio')  || jQ('input[name="'+parentname+'[]"]').is(':checkbox')) {
                            var find = false;
                            var allVals = [];
                            jQ("input:checked").each(function() {
                                for(var i = 0; i < parentvalues.length; i++) {
                                    if (jQ(this).val() == parentvalues[i] && find == false) {
                                        jQ('#advancedsearch-form #f'+child).show();
                                        jQ('#advancedsearch-form #searchfield_'+child).show();
                                        find = true;
                                    }
                                }
                            });

                            if (find == false) {
                                jQ('#advancedsearch-form #f'+child).hide();
                                jQ('#advancedsearch-form #searchfield_'+child).hide();

                                //cleanup child field 
                                if (jQ('#advancedsearch-form #f'+child).is(':checkbox') || jQ('#advancedsearch-form #f'+child).is(':radio')) {
                                    jQ('#advancedsearch-form #f'+child).attr('checked', false);
                                }
                                else {
                                    jQ('#advancedsearch-form #f'+child).val('');
                                }
                            }

                        }
                        //simple text
                        else {
                            var find = false;
                        
                            for(var i = 0; i < parentvalues.length; i++) {
                                if (jQ('#advancedsearch-form #f'+parentname).val() == parentvalues[i] && find == false) {	
                                    jQ('#advancedsearch-form #f'+child).show();
                                    jQ('#advancedsearch-form #searchfield_'+child).show();
                                    find = true;
                                }
                            }
                            
                            if(find == false) {
                                jQ('#advancedsearch-form #f'+child).hide();
                                jQ('#advancedsearch-form #searchfield_'+child).hide();

                                //cleanup child field 
                                if (jQ('#advancedsearch-form #f'+child).is(':checkbox') || jQ('#advancedsearch-form #f'+child).is(':radio')) {
                                    jQ('#advancedsearch-form #f'+child).attr('checked', false);
                                }
                                else {
                                    jQ('#advancedsearch-form #f'+child).val('');
                                }
                            }
                        }
                    }
                    function dependency(child,parentname,parentvalue) {
                        var parentvalues = parentvalue.split(",");
                        
                        //if checkboxes
                        jQ('input[name="'+parentname+'[]"]').change(function() {
                            checkdependency(child,parentname,parentvalues);
                        });
                        //if buttons radio
                        jQ('input[name="'+parentname+'"]').change(function() {
                            checkdependency(child,parentname,parentvalues);
                        });
                        jQ('#f'+parentname).change(function() {
                            checkdependency(child,parentname,parentvalues);
                        });
                        checkdependency(child,parentname,parentvalues);
                    }
                    jQ(document).ready(function() {
                        updateModFields();

                        jQ('#advancedsearch-form #search_catid').change(function(){
                                updateModFields();
                        });

                        <?php foreach($this->simple_fields as $field) { 
                            if (@$field->options->is_conditional_field == 1) { ?>
                            dependency('<?php echo $field->fieldid?>',
                                       '<?php echo $field->options->conditional_parent_name?>',
                                       '<?php echo $field->options->conditional_parent_value?>');
                            <?php } 
                        }?>

                        <?php if(!empty($this->advanced_fields)){ ?>
                        <?php foreach($this->advanced_fields as $field) { 
                            if (@$field->options->is_conditional_field == 1) { ?>
                            dependency('<?php echo $field->fieldid?>',
                                       '<?php echo $field->options->conditional_parent_name?>',
                                       '<?php echo $field->options->conditional_parent_value?>');
                            <?php } 
                        }?>
                        <?php } ?>

                        var updateCounter = function(id) {
                            return function(data, textStatus) {
                                jQ("#"+id).next().html("("+data.count+")");
                            };
                        };
    });
                </script>
                <input type="hidden" value="1" name="new_search" />
                <input type="hidden" value="<?php if (isset($this->alert) && $this->alert != null) { echo $this->alert->id; } ?>" name="id"/>
                <?php if ($this->rootid != 0) {?>
                <input type="hidden" value="<?php echo $this->rootid?>" name="rootid"/>
                <?php } ?>
                </div>
            </fieldset>
        </form>
    </div>
</div>