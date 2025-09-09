<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'tables');

/**
 * @package		Joomla
 * @subpackage	Contact
 */
class AdsmanagerModelExport extends TModel
{
    function getAds($options) {
		
		$cat="";
		$sqlPublished = "";
		$date = "";
		$prefix = " WHERE ";
           
		if(isset($options['category'])){
			$cats = implode(',',$options['category']);
			unset($options['category']);
			$cat = $prefix." catid IN (".$cats.") ";
			$prefix = " AND ";
		}		
		
		if(!(isset($options['published']) && $options['published'] == '1')){
			unset($options['published']);	
			$sqlPublished = $prefix." published = 1";
			$prefix =" AND ";
		}
		

		if(isset($options['dateCreated']) && isset($options['dateEnded'])){
			$dateCreated = $options['dateCreated']." 00:00:00";
			unset($options['dateCreated']);	
			$dateEnded = $options['dateEnded']." 23:59:59";
			unset($options['dateEnded']);
			
			
			$date = $prefix." date_created BETWEEN '".$dateCreated."' AND '".$dateEnded."' ";
			$prefix = " AND ";
		} elseif(isset($options['dateCreated']) && !isset($options['dateEnded'])) {
            $dateCreated = $options['dateCreated']." 00:00:00";
            unset($options['dateCreated']);
            unset($options['dateEnded']);
            
            $date = $prefix." date_created >= '".$dateCreated."' ";
			$prefix = " AND ";
        } elseif(!isset($options['dateCreated']) && isset($options['dateEnded'])) {
            $dateEnded = $options['dateEnded']." 23:59:59";
            unset($options['dateCreated']);
            unset($options['dateEnded']);
            
            $date = $prefix." date_created <= '".$dateEnded."' ";
			$prefix = " AND ";
        } else {
            unset($options['dateCreated']);
            unset($options['dateEnded']);
        }
		
		if(isset($options['images']) && $options['images'] == '1'){
			$selectImage = ",images";
			unset($options['images']);
		} else {
			$selectImage = "";
		}
		
		$select= "";
		foreach ($options as $option){
            $select.= ",".$option;
        }
		$select .= $selectImage;
		
    	$this->_db->setQuery( "SELECT id,catid as category".$select." FROM #__adsmanager_ads AS a INNER JOIN #__adsmanager_adcat AS ac ON ac.adid = a.id ".$cat." ".$sqlPublished." ".$date." ORDER BY date_created DESC" );
		$exports = $this->_db->loadObjectList();
        
        include_once (JPATH_ROOT.'/administrator/components/com_adsmanager/models/category.php');
        $categoryModel = new AdsmanagerModelCategory();
        
        foreach($exports as $key => $export) {
            $exports[$key]->category = $this->formatCategory($export->category);
        }
        
		return $exports;
    }
		
    function formatCategory($catid, $level = '') {
        $sql = "SELECT * FROM #__adsmanager_categories WHERE id = ".$catid;
        $this->_db->setQuery($sql);
        $category = $this->_db->loadObject();
        
        if($level == '') {
            $level = JText::_($category->name);
        } else {
            $level = JText::_($category->name).' >> '.$level;
        }
        
        if($category->parent != 0) {
            $level = $this->formatCategory($category->parent, $level);
        }
        
        return $level;
    }
    
	function getAdminFields($filters = null,$limitstart=null,$limit=null,$filter_order="fieldid",$filter_order_Dir="ASC") {
    	$search= "";
    	if (isset($filters))
    	{
    		foreach($filters as $key => $filter)
    		{
    			if ($search == "")
    				$temp = " WHERE ";
    			else
    				$temp = " AND ";
    			switch($key)
    			{
    				case 'published':
    					if ($filter !== "")
    						$search .= $temp."f.published = ".(int)$filter;
    					break;
    				case 'columnid':
    					if ($filter != "")
    						$search .= $temp."f.columnid = ".(int)$filter;
    					break;
    				case 'pos':
    					if ($filter != "")
    						$search .= $temp."f.pos = ".(int)$filter;
    					break;
    				case 'type':
    					if ($filter != "")
    						$search .= $temp."f.type = ".$this->_db->Quote($filter);
    					break;
    				case 'search':
    					if ($filter != "")
    						$search .= $temp."f.name LIKE ".$this->_db->Quote("%$filter%");
    					break;
    				case 'category':
    					if ($filter != "")
    						$search .= $temp." ((f.catsid = ',-1,') OR (f.catsid LIKE ".$this->_db->Quote("%,$filter,%")."))";
    					break;
    			}
    		}
    	}

    	if (($limitstart === null)||($limit === null))
    		$this->_db->setQuery( "SELECT f.* FROM #__adsmanager_fields as f $search ORDER by f.ordering ASC");
    	else
    		$this->_db->setQuery( "SELECT f.* FROM #__adsmanager_fields as f $search ORDER by $filter_order $filter_order_Dir",
    				$limitstart,$limit );
    	//f.published = 1
    	$fields = $this->_db->loadObjectList('name');
    	foreach($fields as $key => $field) {
    		$fields[$key]->options = json_decode($field->options);
    	}
    	return $fields;
    }
	
	function getField($id) {
		$this->_db->setQuery("SELECT * FROM #__adsmanager_fields WHERE fieldid = ".(int)$id  );
		//echo "SHOW TABLES LIKE '".$mosConfig_dbprefix."comprofiler_fields'" ;
		$field = $this->_db-> loadObject();
		$field->options = json_decode($field->options);
		return $field;
	}
}

	
	

?>