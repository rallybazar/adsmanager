<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'tables');
jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class AdsmanagerControllerExport extends TController
{
	var $_view = null;
	var $_model = null;
	
	function __construct($config= array()) {
		parent::__construct($config);
	
		// Apply, Save & New
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
	}
	
	function init()
	{
		// Set the default view name from the Request
		$this->_view = $this->getView("admin",'html');

		// Push a model into the view
		$this->_model = $this->getModel("export");
		if (!JError::isError( $this->_model )) {
			$this->_view->setModel( $this->_model, true );
		}
		
		$confmodel	  = $this->getModel("configuration");
		$this->_view->setModel( $confmodel );
	}
	
	function display($cachable = false, $urlparams = false)
	{
		$this->init();
		$this->_view->setLayout("export");
		$this->_view->display();
	}
		
	function csv(array &$array)
	{
		$this->canAccess();
	   if (count($array) == 0) {
		 return null;
	   }
	   ob_start();
	   $df = fopen("php://output", 'w');
	   fputcsv($df, array_keys(reset($array)));
	   foreach ($array as $row) {
		  fputcsv($df, $row);
	   }
	   fclose($df);
	   return ob_get_clean();
	}
	
	function download_send_headers($filename) {
		// disable caching
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");

		// force download  
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

		// disposition / encoding on response body
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}
	
	function exportcsv(){
		$this->canAccess();
		include_once (JPATH_ROOT.'/administrator/components/com_adsmanager/models/export.php');
		$model = new AdsmanagerModelExport();
		$array = array();

        if(isset($_POST['field_catsid'])) {
            $array['category'] = $_POST['field_catsid'];
        }
        
        
		$array['published'] = $_POST['published'];
		$array['images'] = $_POST['images'];
        if($_POST['dateCreated'] != ''){
            $array['dateCreated'] = $_POST['dateCreated'];
        }
        if($_POST['dateEnded'] != ''){
            $array['dateEnded'] = $_POST['dateEnded'];
        }
		
		unset($_POST['field_catsid']);
		unset($_POST['dateCreated']);
		unset($_POST['dateEnded']);
		unset($_POST['published']);
		unset($_POST['images']);
		
		foreach($_POST as $key =>$value){
			if(is_array($value)){
				$array[] = implode(',',$value);
				
			}else{
				$array[] = $value;
			}
			
			$array = array_filter($array);

		}
		
		$array_csv = $model->getAds($array);
		
	
		$fileName = 'AdsManager_Ads.csv';
 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");

		$fh = @fopen( 'php://output', 'w' );

		$headerDisplayed = false;
		
		$maxImageCount = 0;
		foreach($array_csv as $key => $row) {
			if(isset($row->images)) {
				$rImages = json_decode($row->images);
				$i = 1;
				foreach($rImages as $rImage) {
					$iName = 'image'.$i;
					$array_csv[$key]->$iName = JUri::root().'images/com_adsmanager/contents/'.$rImage->image;
					
					if($maxImageCount < $i) {
						$maxImageCount = $i;
					}
					$i++;
				}
				unset($row->images);
			}
		}
		
		foreach ( $array_csv as $row ) {
			
			$row_array = (array)$row;
			if ( !$headerDisplayed ) {
				for($imageCount = 1; $imageCount <= $maxImageCount; $imageCount++) {
					$imageName = 'image'.$imageCount;
					if(!isset($row->$imageName)) {
						$row_array[$imageName] = '';
					}
				}
				// Use the keys from $data as the titles
				fputcsv($fh, array_keys($row_array), ";");
				$headerDisplayed = true;
			}
			
			fputcsv($fh, $row_array, ";");
		}

		// Close the file
		fclose($fh);
		// Make sure nothing else is sent, our file is done
		exit;

	}

	private function canAccess() {
		//check if the user can access the export page
		$user = JFactory::getUser();
		if(!$user->authorise('adsmanager.accessexport','com_adsmanager')) {
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		return true;
	}
}
