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
    <?php
    $conf= $this->conf;
    ?>
    <div class="row-fluid">
        <fieldset>
            <legend><?php echo JText::_('ADSMANAGER_MY_ALERTS');?></legend>
        </fieldset>
    </div>
    <div class="row-fluid">
        <a href="<?= TRoute::_('index.php?option=com_adsmanager&view=alert')?>" class="btn btn-primary" type="button"><?php echo JText::_('ADSMANAGER_ALERT_ADD_ALERT'); ?></a>
    </div>
    <br/>
    <?php
    if ($this->pagination->total == 0 ) 
    {
        echo JText::_('ADSMANAGER_NO_ALERT_ENTRIES'); 
    }
    else
    {
        ?>
        <div class="container-fluid">
            <table class="adsmanager_table table table-striped">
                <tr>
                  <th class="hidden-phone"><?php echo JText::_('ADSMANAGER_ALERT_DETAILS'); ?>
                  </th>
                  <th class="hidden-phone"><?php echo JText::_('ADSMANAGER_ALERT_RECURRENCE'); ?>
                  </th>
                  <th class="hidden-phone"><?php echo JText::_('ADSMANAGER_ACTIONS'); ?>
                  </th>
                </tr>
            <?php
            foreach($this->contents as $content) 
            {

                $linkTarget = TRoute::_( "index.php?option=com_adsmanager&view=alert&id=".$content->id);
                ?>   
                <tr> 
                    <td class="tdcenter">
                    <?php
	                    if ($content->catid) {
	                    	echo "<strong>".JText::_('ADSMANAGER_CATEGORY')."</strong>: ".$this->categories[$content->catid]->name."<br/>";
	                    }
	                    $c = (object) $content->fields;
                     	foreach($content->fields as $fieldname => $value) {
                    	if (strpos($fieldname,"ad_") === 0) {
							if ($value != "") {
								if (strrpos($fieldname,"_min") !== false) {
									$fieldname2 = substr($fieldname,0,strpos($fieldname,"_min"));
									$c->$fieldname2 = $c->$fieldname;
									$f = $this->fields[$fieldname2];
									$f->catsid = ",-1,";
									$val = $this->field->showFieldValue($c,$f);
									echo "<strong>".TText::_($f->title)." ".TText::_('ADSMANAGER_MYALERTS_MIN')."</strong>: ".$val."<br/>";
								} else if (strpos($fieldname,"_max") !== false) {
									$fieldname2 = substr($fieldname,0,strpos($fieldname,"_max"));
									$c->$fieldname2 = $c->$fieldname;
									$f = $this->fields[$fieldname2];
									$f->catsid = ",-1,";
									$val = $this->field->showFieldValue($c,$f);
									echo "<strong>".TText::_($f->title)." ".TText::_('ADSMANAGER_MYALERTS_MAX')."</strong>: ".$val."<br/>";
								} else {
									$f = $this->fields[$fieldname];	
									$f->catsid = ",-1,";
									if (is_array($c->$fieldname)) {
										$c->$fieldname =','.implode(',',$c->$fieldname).',';
									}
									$val = $this->field->showFieldValue($c,$f);
									echo "<strong>".TText::_($f->title)."</strong>: ".$val."<br/>";
								}
							}
						}  		
            		}?>  
                    </td>
                    <td class="tdcenter hidden-phone">
                      <?php switch($content->recurrence) {
                      	case 'oneveryad': echo JText::_('ADSMANAGER_RECURRENCE_ONEVERYAD');break;
                      	case 'everyhour': echo JText::_('ADSMANAGER_RECURRENCE_EVERYHOUR');break;
                      	case 'every12hours': echo JText::_('ADSMANAGER_RECURRENCE_EVERY12HOURS');break;
                      	case 'everyday': echo JText::_('ADSMANAGER_RECURRENCE_EVERYDAY');break;
                      	case 'everyweek': echo JText::_('ADSMANAGER_RECURRENCE_EVERYWEEK');break;
                      }?>
                    </td>
                    <td class="tdcenter">
                        <?php
                        $target = TRoute::_("index.php?option=com_adsmanager&view=alert&id=$content->id");
                        echo "<a href='".$target."'>".JText::_('ADSMANAGER_CONTENT_EDIT')."</a>";
                        echo "<br/>";
                        $target = TRoute::_("index.php?option=com_adsmanager&task=deletealert&id=$content->id");
                        echo "<a onclick='return confirm(\"".htmlspecialchars(JText::_('ADSMANAGER_ALERT_CONFIRM_DELETE'),ENT_QUOTES)."\")' href='".$target."'>".JText::_('ADSMANAGER_CONTENT_DELETE')."</a>";
                        ?>
                    </td>
                </tr>
            <?php	
            }
            ?>
            </table>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <?php echo $this->pagination->getResultsCounter() ?>
            </div>
        </div>
    <?php 
    } $this->general->endTemplate();
    ?>
</div>
