<?php
/**
 *  @package	PaidSystem
 *  @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 *  @license    GNU General Public License version 3, or later
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$formId = "advsearchmoduleform".$moduleId;
?>
<div class="juloawrapper">
    <div class="row-fluid">
    <?php
        $input = JFactory::getApplication()->input;
        $itemid = $input->get('Itemid',0,'integer');     
        if($itemid != 0 && $keepItemid != 0) {
			$link = TRoute::_("index.php?option=com_adsmanager&view=result&Itemid=".$itemid);
		} else {
			$link = TRoute::_("index.php?option=com_adsmanager&view=result");
		}
    ?>
    <form action="<?php echo $link; ?>" id="<?php echo $formId; ?>" class="advsearchmoduleform" method="post">
    <div class="adsmanager_search_box<?php echo $moduleclass_sfx; ?>">
                <fieldset>
                    <div class="row-fluid">
    <?php if ($search_by_text == 1){ ?>
                            <div class="span12">
                                <div class="control-group">
                                    <label class="control-label" for="tsearch"></label>
                                    <div class="controls">
                                        <input type="text" name="tsearch" placeholder="<?php echo JText::_('ADSMANAGER_LIST_SEARCH'); ?>" value="<?php echo $text_search; ?>" />
                                    </div>
                                </div>
                            </div>
    <?php } ?>
    <?php if ($search_by_cat == 1){ ?>
                        <div class="span12">
                        <div class="control-group">
                            <?php if($display_cat_label == 1): ?>
                                <label class="control-label" for="catid"><?php echo JText::_('ADSMANAGER_SELECT_CATEGORY_LABEL') ?></label>
                            <?php endif; ?>
                            <div class="controls">
    <?php 
        switch($conf->single_category_selection_type) {
            default:
            case 'normal':
                JHTMLAdsmanagerCategory::displayNormalCategories("catid",$cats,$catid,array("allow_empty"=>true,"id"=>"catid-".$moduleId));break;
            case 'color':
                JHTMLAdsmanagerCategory::displayColorCategories("catid",$cats,$catid,array("allow_empty"=>true,"id"=>"catid-".$moduleId));break;
            case 'combobox':
                JHTMLAdsmanagerCategory::displayComboboxCategories("catid",$cats,$catid,array("allow_empty"=>true,"id"=>"catid-".$moduleId));break;
                break;
            case 'cascade':
                if ($type == "horizontal") 
                    $separator = "";
                else
                    $separator = "<br/>";
                JHTMLAdsmanagerCategory::displaySplitCategories("catid",$cats,$catid,array('separator'=>$separator,"id"=>"catid-".$moduleId));break;
        }
    ?>
    </div>
                        </div>
                        </div>
    <?php } ?>
                    </div>
                        <div class="row-fluid">
    <?php 
    foreach($simple_fields as $fsearch) {
                                echo "<div id='modsearchfield_$fsearch->name' class=\"span12\">";
                                echo "<div class=\"control-group\">";
                                echo "<label class=\"control-label\" for=\"{$fsearch->name}\">".JText::_($fsearch->title)."</label>";
                                echo "<div class=\"controls\">";
        $field->showFieldSearch($fsearch,0,$defaultvalues,true);
                                echo "</div>";
                                echo "</div>";
        echo "</div>";
    } ?>
                        </div>
    <?php if(!empty($advanced_fields)){ ?>
                        <div id="togglemode" class="row-fluid">
                            <div class="span12">
    <a href="#" id="togglesearch">
    <?php echo JText::_('ADSMANAGER_ADVANCED_SEARCH')?>&nbsp;
    <img class="togglesearchimg" src="<?php echo JURI::base()."modules/mod_adsmanager_advancedsearch/img/arrow_down.png"?>" />
    </a>
    </div>
                        </div>
                        <div id="advancedsearch" class="row-fluid">
    <?php 
    foreach($advanced_fields as $fsearch) {
                                echo "<div id='modsearchfield_$fsearch->name' class=\"span12\">";
                                echo "<div class=\"control-group\">";
                                echo "<label class=\"control-label\" for=\"{$fsearch->name}\">".JText::_($fsearch->title)."</label>";
                                echo "<div class=\"controls\">";
        $field->showFieldSearch($fsearch,0,$defaultvalues,true);
                                echo "</div>";
                                echo "</div>";
        echo "</div>";
    } ?>
    </div>
    <?php } ?>
    <input type="button" id="submitsearchform" class="btn btn-primary" value="<?php echo JText::_('ADSMANAGER_SEARCH_TITLE'); ?>"/>
    <script type="text/javascript">
    jQ('#<?php echo $formId; ?> #submitsearchform').click(function(){
    	jQ('#<?php echo $formId; ?> input[type!="hidden"]:hidden').attr("disabled",true);
    	jQ('#<?php echo $formId; ?> select:hidden').attr("disabled",true);
        jQ('#<?php echo $formId; ?>').submit();
    });
    
    function updateModFields<?php echo $moduleId; ?>() {
        var form = document.<?php echo $formId; ?>;
        catid = jQ('#<?php echo $formId; ?> #catid-<?php echo $moduleId; ?>').val();
        <?php
        $fields = array_merge($simple_fields,$advanced_fields);
        foreach($fields as $field)
        { 	
            if (strpos($field->catsid, ",-1,") === false)
            {
            ?>
            var field_condition = "<?php echo $field->catsid;?>";
            var test = field_condition.indexOf( ","+catid+",", 0 );
                var divfield = jQ('#<?php echo $formId; ?> #modsearchfield_<?php echo $field->name;?>');
            if (test != -1) {
                <?php if (@$field->options->is_conditional_field == 1) { ?>
				dependency('<?php echo $field->name?>',
						   '<?php echo $field->options->conditional_parent_name?>',
						   '<?php echo $field->options->conditional_parent_value?>',
						   '<?php echo $formId; ?>');
				<?php }else{ ?>
					jQ('#<?php echo $formId; ?> #modsearchfield_<?php echo $field->name;?>').show();
				<?php } ?>
            }
            else {
                jQ('#<?php echo $formId; ?> #modsearchfield_<?php echo $field->name;?>').hide();
            }
        <?php
            }
        } 
        ?>
    }
    
    function checkdependency(child,parentname,parentvalues,containerId) {

    	containerId = containerId || '';
        
        //Simple checkbox
        if (jQ('#'+containerId+' input[name="'+parentname+'"]').is(':checkbox')) {
            //alert("test");
            if (jQ('#'+containerId+' input[name="'+parentname+'"]').is(':checked')) {
                jQ('#'+containerId+' #f'+child).show();
                jQ('#'+containerId+' #modsearchfield_'+child).show();
            }
            else {
                jQ('#'+containerId+' #f'+child).hide();
                jQ('#'+containerId+' #modsearchfield_'+child).hide();

                //cleanup child field 
                if (jQ('#'+containerId+' #f'+child).is(':checkbox') || jQ('#'+containerId+' #f'+child).is(':radio')) {
                    jQ('#'+containerId+' #f'+child).attr('checked', false);
                }
                else {
                    jQ('#'+containerId+' #f'+child).val('');
                }
            } 
        }
        //If checkboxes or radio buttons, special treatment
        else if (jQ('#'+containerId+' input[name="'+parentname+'"]').is(':radio')  || jQ('#'+containerId+' input[name="'+parentname+'[]"]').is(':checkbox')) {
            var find = false;
            var allVals = [];
            jQ("#'+containerId+' input:checked").each(function() {
                for(var i = 0; i < parentvalues.length; i++) {
                    if (jQ(this).val() == parentvalues[i] && find == false) {
                        jQ('#'+containerId+' #f'+child).show();
                        jQ('#'+containerId+' #modsearchfield_'+child).show();
                        find = true;
                    }
                }
            });

            if (find == false) {
                jQ('#'+containerId+' #f'+child).hide();
                jQ('#'+containerId+' #modsearchfield_'+child).hide();

                //cleanup child field 
                if (jQ('#'+containerId+' #f'+child).is(':checkbox') || jQ('#'+containerId+' #f'+child).is(':radio')) {
                    jQ('#'+containerId+' #f'+child).attr('checked', false);
                }
                else {
                    jQ('#'+containerId+' #f'+child).val('');
                }
            }

        }
        //simple text
        else {
            var find = false;

            for(var i = 0; i < parentvalues.length; i++) {
                if (jQ('#'+containerId+' #f'+parentname).val() == parentvalues[i] && find == false) {	
                    jQ('#'+containerId+' #f'+child).show();
                    jQ('#'+containerId+' #modsearchfield_'+child).show();
                    find = true;
                }
            }
            
            if(find === false) {
                jQ('#'+containerId+' #f'+child).hide();
                jQ('#'+containerId+' #modsearchfield_'+child).hide();

                <?php
                    $fields = array_merge($simple_fields,$advanced_fields);
                    foreach($fields as $field)
                    {
                        if (@$field->options->is_conditional_field == 1) { ?>
                            if('<?php echo $field->options->conditional_parent_name ?>' == child) {
                                jQ('#'+containerId+' #f<?php echo $field->name ?>').hide();
                                jQ('#'+containerId+' #modsearchfield_<?php echo $field->name ?>').hide();
                            }
                            <?php
                        }
                    }
                ?>

                //cleanup child field 
                if (jQ('#'+containerId+' #f'+child).is(':checkbox') || jQ('#'+containerId+' #f'+child).is(':radio')) {
                    jQ('#'+containerId+' #f'+child).attr('checked', false);
                }
                else {
                    jQ('#'+containerId+' #f'+child).val('');
                }
            }
        }
    }
    function dependency(child,parentname,parentvalue,containerId) {

		containerId = containerId || '';
        
        var parentvalues = parentvalue.split(",");

        //if checkboxes
        jQ('#'+containerId+' input[name="'+parentname+'[]"]').change(function() {
            checkdependency(child,parentname,parentvalues,containerId);
        });
        //if buttons radio
        jQ('#'+containerId+' input[name="'+parentname+'"]').change(function() {
            checkdependency(child,parentname,parentvalues,containerId);
        });
        jQ('#'+containerId+' #f'+parentname).change(function() {
            checkdependency(child,parentname,parentvalues,containerId);
        });
        checkdependency(child,parentname,parentvalues,containerId);
    }
    
    jQ(document).ready(function() {
        updateModFields<?php echo $moduleId; ?>();

        jQ('#<?php echo $formId; ?> #catid-<?php echo $moduleId; ?>').change(function(){
            updateModFields<?php echo $moduleId; ?>();
        });

        <?php foreach($simple_fields as $field) { 
            if (@$field->options->is_conditional_field == 1) { ?>
            dependency('<?php echo $field->name?>',
                       '<?php echo $field->options->conditional_parent_name?>',
                       '<?php echo $field->options->conditional_parent_value?>',
                       '<?php echo $formId; ?>');
            <?php } 
        }?>

        <?php if(!empty($advanced_fields)){ ?>
        <?php foreach($advanced_fields as $field) { 
            if (@$field->options->is_conditional_field == 1) { ?>
            dependency('<?php echo $field->name?>',
                       '<?php echo $field->options->conditional_parent_name?>',
                       '<?php echo $field->options->conditional_parent_value?>',
                       '<?php echo $formId; ?>');
            <?php } 
        }?>
        <?php } ?>

        jQ('#<?php echo $formId; ?> #togglesearch').click(function(){
            if (jQ('#<?php echo $formId; ?> #advancedsearch').is(":visible")) {
                jQ('.togglesearchimg').attr('src','<?php echo JURI::base()."modules/mod_adsmanager_advancedsearch/img/arrow_down.png"?>');
    			jQ('#<?php echo $formId; ?> #advancedsearch').hide(100);
    			jQ('#<?php echo $formId; ?> #advsearch').val(0);
            } else {
                /*if (jQ('#catid').val() == "") {
                    alert(<?php echo json_encode(JText::_('ADSMANAGER_MUST_SELECT_CATEGORY_FIRST'))?>);
                    return false;
                }*/
                jQ('.togglesearchimg').attr('src','<?php echo JURI::base()."modules/mod_adsmanager_advancedsearch/img/arrow_up.png"?>');
    			jQ('#<?php echo $formId; ?> #advancedsearch').show(100);
    			jQ('#<?php echo $formId; ?> #advsearch').val(1);
            }
            
            return false;
        });
    	<?php if ($advsearch == 1) { ?>
    	jQ('.togglesearchimg').attr('src','<?php echo JURI::base()."modules/mod_adsmanager_advancedsearch/img/arrow_up.png"?>');
    	jQ('#<?php echo $formId; ?> #advancedsearch').show(100);
    	jQ('#<?php echo $formId; ?> #advsearch').val(1);
    	<?php } ?>

        var updateCounter = function(id) {
            return function(data, textStatus) {
                jQ("#<?php echo $formId; ?> #"+id).next().html("("+data.count+")");
            };
        };


    });
    </script>
    <input type="hidden" value="0" name="advsearch" id="advsearch" />
    <input type="hidden" value="1" name="new_search" />
    <?php if ($rootid != 0) {?>
    <input type="hidden" value="<?php echo $rootid?>" name="rootid"/>
    <?php } ?>
                </fieldset>
            </div>
        </form>
    </div>
</div>