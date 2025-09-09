<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die();

class AdsmanagerTableSearchpage extends JTable
{
	var $id = null;
	var $params;
	
    function __construct(&$db)
    {
    	parent::__construct( '#__adsmanager_searchpage_config', 'id', $db );
    }
    
    function bind($data,$ignore=array()) {
    	parent::bind($data,$ignore);
    	
    	// All post values starting with params_ should be saved in json format in params
    	$tmp_params = array();
    	foreach($data as $key => $d) {
    		if (strpos($key,"params_") === 0) {
    			$k = substr($key,7);
    			$tmp_params[$k] = $d;
    		}
    	}
    	$this->params = json_encode($tmp_params);
    	return true;
    }
}
