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
    <div id="adsmanager_global_filter" class="row-fluid adsmanager_global_filter_mod<?php echo $moduleclass_sfx; ?>">
        <form action="<?php echo $link; ?>" method="post" id="globalfilterform">
        <div class="span12">
            <div class="control-group">
                <label class="control-label" for="catid"></label>
                <div class="controls">
                <?php
                    $fhelpler->showFieldSearch($field,$catid,$defaultvalues);
                ?>
                </div>
            </div>
        </div>
        <input type="hidden" name="global_filter" id="global_filter" value="1" />
        </form>
    </div>
    <script>
    jQ('.adsmanager_global_filter_mod #f<?php echo $field->name?>').change(function() {
        jQ('#globalfilterform').submit();
    });
    </script>
</div>