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
<div class="cpanel-links">
					<div class="sidebar-nav quick-icons">
		<div class="j-links-groups">
<h2 class="nav-header">AdsManager</h2>
<ul class="j-links-group nav nav-list">
<?php foreach($links as $link) {?>
<li id="plg_quickicon_joomlaupdate">
	<a href="<?php echo $link->link ?>">
		<img alt="<?php echo $link->text; ?>" src="<?php echo $link->img ?>" /> <?php echo $link->text; ?>	</a>
</li>
<?php } ?>
</ul>
</div></div></div>