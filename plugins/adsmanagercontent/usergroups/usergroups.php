<?php
/**
 * @package		 Adsmanager Plugins
 * @subpackage	 Social
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * ITPShare is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT.'/administrator/components/com_adsmanager/models/configuration.php');

require_once(JPATH_ROOT."/components/com_adsmanager/lib/core.php");

jimport('joomla.plugin.plugin');

/**
 * ITPShare Plugin
 *
 * @package		Adsmanager Plugins
 * @subpackage	Social
 * @since 		1.5
 */
class plgAdsmanagercontentUsergroups extends JPlugin {
    
    public function ADSonSqlFilter () {
        $type = JRequest::getString('type', '');
        
        if($type == '') {
            return false;
        }
        
        $where = "u.id IN (SELECT uum.user_id FROM #__user_usergroup_map uum
                           INNER JOIN #__usergroups ug
                            ON uum.group_id = ug.id
                           WHERE title = '".$type."')";
                               
        $return = array();
        $return[] = $where;
        
        return $return;
    }
    
}