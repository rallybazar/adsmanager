<?php
/**
 *  @package	PaidSystem
 *  @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 *  @license    GNU General Public License version 3, or later
 */
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

if($content == null) {} else {
?>
<div class="juloawrapper">
    <table class='table table-bordered contact_ads<?php echo $moduleclass_sfx; ?>'>
        <tr>
            <th><h4></h4></th>
        </tr>
        <tr><td>
                <?php echo $user->cb_type; 
                echo '<br>';
                $c = $fieldhelper->showFieldValue($content,$fields['ad_type']); 
                if ($c != "") {
                    $title = $fieldhelper->showFieldTitle(@$content->catid,$fields['ad_type']);
                    if ($title != "")
                    echo htmlspecialchars($title).": ";
                    echo "$c ";
                } ?>	
        </td></tr>
        <tr><td>
                <?php echo $thumbnailAvatarHtmlWithLink.' '.$user->lastname.' '.$user->firstname; 
                echo '<br>';
                $c = $fieldhelper->showFieldValue($content,$fields['name']); 
                if ($c != "") {
                    $title = $fieldhelper->showFieldTitle(@$content->catid,$fields['name']);
                    if ($title != "")
                    echo htmlspecialchars($title).": ";
                    echo "$c ";
                } ?>
        </td></tr>
        <tr><td>
                <?php echo $user->cb_phone; 
                echo '<br>';
                $c = $fieldhelper->showFieldValue($content,$fields['ad_phone']); 
                if ($c != "") {
                    $title = $fieldhelper->showFieldTitle(@$content->catid,$fields['ad_phone']);
                    if ($title != "")
                    echo htmlspecialchars($title).": ";
                    echo "$c ";
                } ?>
        </td></tr>
        <tr><td>
                <?php echo $user->email; 
                echo '<br>';
                $c = $fieldhelper->showFieldValue($content,$fields['email']); 
                if ($c != "") {
                    $title = $fieldhelper->showFieldTitle(@$content->catid,$fields['email']);
                    if ($title != "")
                    echo htmlspecialchars($title).": ";
                    echo "$c ";
                } ?>
        </td></tr>
        <tr><td>
                <?php
                    $pmsText= sprintf(JText::_('ADSMANAGER_PMS_FORM'),$content->user);
                    $pmsForm = JRoute::_("index.php?option=com_uddeim&task=new&recip=".$content->userid);
                    echo '<a href="'.$pmsForm.'">'.$pmsText.'</a><br />';	
                ?>
        </td></tr>
        <tr><td> 
                <?php
                $c = $fieldhelper->showFieldValue($content,$fields['ad_gmap']); 
                if ($c != "") {
                    $title = $fieldhelper->showFieldTitle(@$content->catid,$fields['ad_gmap']);
                    if ($title != "")
                    echo htmlspecialchars($title).": ";
                    echo "$c ";
                } ?>
        </td></tr>	
    </table>

    <style>
    #map_canvas<?php echo $content->id.'_'.$fields['ad_gmap']->fieldid ?> {
        width:230px !important;
        height:300px !important;
    }
    </style>
</div>
<?php } ?>

