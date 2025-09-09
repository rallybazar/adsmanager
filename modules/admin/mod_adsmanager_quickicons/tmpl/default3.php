<?php
/**
 * @package Adsmanager
 * @subpackage AdsmanagerIconModule
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license GNU General Public License version 3, or later
 * @since 2.2
 * @version 1.0
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="row-striped">
  <?php foreach($links as $link) {?>
  <div class="row-fluid">
    <div class="span12">
	    <a href="<?php echo $link->link ?>">
		    <img alt="<?php echo $link->text; ?>" src="<?php echo $link->img ?>" />
		    <span><?php echo $link->text; ?></span>
	    </a>
    </div>
  </div>
  <?php } ?>
</div>