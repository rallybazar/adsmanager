<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport( 'joomla.filesystem.file' );

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'tables');
include_once (JPATH_ROOT.'/components/com_adsmanager/helpers/emailtemplates.php');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class AdsManagerController extends TController
{
	function display($cachable = false, $urlparams = false)
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$user		= JFactory::getUser();

		if ( ! JRequest::getCmd( 'view' ) ) {
			$default	= 'front';
			JRequest::setVar('view', $default );
		}

        if(version_compare(JVERSION, '3.0', 'ge')) {
		$viewLayout = $this->input->get('layout', 'default', 'string');
        } else {
            $viewLayout = JRequest::getVar('layout', 'default', 'string');
        }
		//$viewLayout = "ouacheteroutrouver:write";

		$viewName  = JRequest::getVar('view', 'front', 'default', 'cmd');
		$type	   = JRequest::getVar('format', 'html', 'default', 'cmd');
		$view      = $this->getView($viewName,$type,'',array('layout' => $viewLayout));

		if ($viewName == "edit")
		{
			$this->write();
			return;
		}

		$uri = JFactory::getURI();
		$baseurl = JURI::base();
		$view->assign("baseurl",$baseurl);
		$view->assignRef("baseurl",$baseurl);

		// Push a model into the view
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');

		$contentmodel	=$this->getModel( "content" );
		$alertmodel	    = $this->getModel( "alert" );
		$catmodel		=$this->getModel( "category" );
		$positionmodel	=$this->getModel( "position" );
		$columnmodel	=$this->getModel( "column" );
		$fieldmodel	    =$this->getModel( "field" );
		$usermodel		=$this->getModel( "user" );
		$adsmanagermodel=$this->getModel( "adsmanager" );
        $conf = TConf::getConfig();

		loadAdsManagerCss();

		if (!JError::isError( $contentmodel )) {
			$view->setModel( $contentmodel, true );
		}

		$view->setModel( $contentmodel);
		$view->setModel( $catmodel);
		$view->setModel( $alertmodel);
		$view->setModel( $positionmodel);
		$view->setModel( $columnmodel);
		$view->setModel( $fieldmodel);
		$view->setModel( $usermodel);
		$view->setModel( $adsmanagermodel);

		if ((ADSMANAGER_SPECIAL == "abrivac") &&
			((JRequest::getCmd( 'view' ) == 'front')||(JRequest::getCmd( 'view' ) == 'rules')))
			return;

		if ($conf->crontype == "onrequest") {
			$this->cron();
		}

		if ($viewName == "details") {
			$contentid = JRequest::getInt( 'id',	0 );
			$content = $contentmodel->getContent($contentid,false);
			// increment views. views from ad author are not counted to prevent highclicking views of own ad
			if ( $user->id <> $content->userid || $content->userid==0) {
				$contentmodel->increaseHits($content->id);
			}
		}

		if (($viewName == "list")&&($user->get('id')==0)&&(JRequest::getInt( 'user',	-1 ) == 0)) {
			TTools::redirectToLogin("index.php?option=com_adsmanager&view=list&user=");
	    	return;
		}


		if ($user->get('id'))
		{
			parent::display(false);
		}
		else if ($viewName == "result")
		{
			parent::display(false);
		}
		else if ($viewName == "list")
		{
			$cache = JFactory::getCache( 'com_adsmanager' );
			$method = array( $view, 'display' );

			$session = JFactory::getSession();
			$tsearch = JRequest::getVar( 'tsearch',	$session->get('tsearch','','adsmanager'));
			$limit   = $conf->ads_per_page;
			$order   = $app->getUserStateFromRequest('com_adsmanager.front_content.order','order',0,'int');
			$mode    = $app->getUserStateFromRequest('com_adsmanager.front_content.mode','mode',$conf->display_expand);
			$url = $uri->toString();

			//Fix needed in case of cache activated otherwise addScript is not added by gmap module
			$conf = TConf::getConfig();
			if(@$conf->display_map_list == 1){
				$document = JFactory::getDocument();
				$document->addScript(JURI::root().'components/com_adsmanager/js/jquery.cookie.js');
			}

			echo $cache->call( $method, null,$url,$tsearch,$limit,$order,$mode) . "\n";
		}
		else
		{
			parent::display(true);
		}

		$path = JPATH_ADMINISTRATOR.'/../libraries/joomla/database/table';
		JTable::addIncludePath($path);
	}

	function reloadForm($content,$errorMsg="") {
		$errors = $content->getErrors();
		if (is_array($errors) && count($errors) > 0 )
			$error_msg = htmlspecialchars(implode("<br/>",$errors));
		else
			$error_msg = htmlspecialchars($errorMsg);

		$catid = JRequest::getInt('category', 0 );
		if ($_SERVER['HTTP_REFERER'] != "") {
			$url = $_SERVER['HTTP_REFERER'];
		} else {
		$url = TRoute::_("index.php?option=com_adsmanager&task=write&catid=$catid");
		}
		echo "<form name='form' action='$url' method='post'>";
		foreach(JRequest::get( 'post' ) as $key=>$val)
		{
			if (is_array($val))
				$val = implode(',',$val);
			echo "<input type='hidden' name='$key' value=\"".htmlspecialchars($val)."\">";
		}
		echo "<input type='hidden' name='errorMsg' value='$error_msg'>";
		echo '</form>';
		echo '<script language="JavaScript">';
		echo 'document.form.submit()';
		echo '</script>';
		exit();
	}

	function write($duplicate=false)
	{
		$app = JFactory::getApplication();

		$document = JFactory::getDocument();

		// Set the default view name from the Request
		$type = "html";

		$uri = JFactory::getURI();
		$baseurl = JURI::base();

		// Push a model into the view
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$catmodel		    =$this->getModel( "category" );
		$contentmodel		=$this->getModel( "content" );
		$positionmodel		=$this->getModel( "position" );
		$fieldmodel			=$this->getModel( "field" );
		$usermodel			=$this->getModel( "user");
		$user = JFactory::getUser();
		$conf = TConf::getConfig();

		loadAdsManagerCss();

		JuloaLib::loadJqueryUI();


		/* submission_type = 1 -> Account needed */
	    if (($conf->submission_type == 1)&&($user->id == "0")) {
	    	TTools::redirectToLogin("index.php?option=com_adsmanager&task=write");
	    	return;
	    }
	    else
	    {
		    $contentid = JRequest::getInt( 'id', 0 );
		    $nbcontents = $contentmodel->getNbContentsOfUser($user->id);

			if (($contentid == 0)&&($user->id != "0")&&($conf->nb_ads_by_user != -1)&&($nbcontents >= $conf->nb_ads_by_user))
			{
				//REDIRECT
				$redirect_text = sprintf(JText::_('ADSMANAGER_MAX_NUM_ADS_REACHED'),$conf->nb_ads_by_user);
				$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text ,'message');
			}
			else
			{
				$view = $this->getView("edit",'html');
				$view->setModel( $contentmodel, true );
				$view->setModel( $catmodel );
				$view->setModel( $fieldmodel );
				$view->setModel( $usermodel );
				$view->setModel( $positionmodel );

				$uri = JFactory::getURI();
				$baseurl = JURI::base();
				$view->assign("baseurl",$baseurl);
				if ($duplicate == true) {
					$isDuplicated = 1;
					$view->assign("isDuplicated",1);
				}

				$view->display();
			}
	    }
	    $path = JPATH_ADMINISTRATOR.'/../libraries/joomla/database/table';
		JTable::addIncludePath($path);
	}

    function duplicate()
	{
		$this->write(true);
	}

	function updatedate() {
		//OUTROUVER
		exit();
		$app = JFactory::getApplication();
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$contentmodel = $this->getModel( "content" );
		$contentid = JRequest::getInt( 'id', 0 );
		$contentmodel->updatedate($contentid);
		$app->redirect( TLink::getMyAdsLink(), JText::_('ADSMANAGER_DATE_UPDATED') ,'message');
	}



	/**
	* Saves the content item an edit form submit
	*
	* @todo
	*/
	function save()
	{
		$app = JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$user = JFactory::getUser();
		$content = JTable::getInstance('contents', 'AdsmanagerTable');

		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');

		$contentmodel		=$this->getModel( "content" );
		$alertmodel         =$this->getModel("alert");
		$usermodel			=$this->getModel( "user" );
		$fieldmodel 		=$this->getModel("field");
		$conf = TConf::getConfig();
		$plugins = $fieldmodel->getPlugins();

		$id = JRequest::getInt( 'id', 0 );
        $preview = JRequest::getInt('preview', 0);

		//Creation of account if needed
		if (($conf->submission_type == 0)&&($user->id == 0))
		{
			$username = JRequest::getVar('username', "" );
			$password = JRequest::getVar('password', ""  );
			$email = JRequest::getVar('email', ""  );
			$errorMsg = $usermodel->checkAccount($username,$password,$email,$userid,$conf);
			if (isset($errorMsg))
			{
				$this->reloadForm($content,$errorMsg);
				return;
			}
			$user->id = $userid;
		}

		// New or Update
		if ($id != 0) {
			$content->load($id);
			if (($content == null)||($content->userid != $user->id)) {
				$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list') );
			}

			$isUpdateMode = 1;
			if ($conf->update_validation == 1) {
				$redirect_text = JText::_('ADSMANAGER_INSERT_SUCCESSFULL_CONFIRM');
			} else {
				$redirect_text = JText::_('ADSMANAGER_AD_UPDATED');
			}
		} else {
			$isUpdateMode = 0;
			if ($conf->auto_publish == 0)
				$redirect_text = JText::_('ADSMANAGER_INSERT_SUCCESSFULL_CONFIRM');
			else
				$redirect_text = JText::_('ADSMANAGER_INSERT_SUCCESSFULL_PUBLISH');
		}

		//Check Max Ads by User
        $nbcats = $conf->nbcats;
		if (function_exists("getMaxCats"))
		{
			$nbcats = getMaxCats($conf->nbcats);
		}
        if($nbcats <= 1){
            if (function_exists("checkAuthorisedNumberAds")){
                $limitAds = checkAuthorisedNumberAds($contentmodel, JRequest::getInt( 'category', 0 ));
                if($limitAds !== true){
                    $redirect_text = sprintf(JText::_('ADSMANAGER_MAX_NUM_ADS_PER_CATEGORY_REACHED'),$limitAds);
                    $app->redirect(TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text,'message' );
                }
            } else {

                if(JRequest::getInt( 'category', 0 ) != 0) {

                	$category = JRequest::getInt( 'category', 0 );
                	// Need to check limit only for new ad and if category of ad is changed
                	if (($isUpdateMode == 0)||
                		(!in_array($category,$contentmodel->getContentCategories($id))))
                	{
	                    $nb = $contentmodel->getNbContentsOfUser($user->id, $category);

	                    //TODO : check authorised number for multi-categories
	                    $categoriesModel = $this->getModel( "category" );

	                    $category = $categoriesModel->getCategory($category);

	                    if (($category->limitads !== "")&& ($category->limitads !== null)) {
		                    if($nb >= $category->limitads && $category->limitads != -1){
		                        $redirect_text = sprintf(JText::_('ADSMANAGER_MAX_NUM_ADS_PER_CATEGORY_REACHED'),$category->limitads);
		                        $app->redirect(TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text,'message' );
		                    }
	                    }
                	}
                }
            }
        }
		if (($id == 0)&&($user->id != "0")&&($conf->nb_ads_by_user != -1))
		{
			$nb = $contentmodel->getNbContentsOfUser($user->id);
			if ($nb >= $conf->nb_ads_by_user)
			{
				$redirect_text = sprintf(JText::_('ADSMANAGER_MAX_NUM_ADS_REACHED'),$conf->nb_ads_by_user);
				$app->redirect(TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text,'message' );
			}
		}

        //check if user can post an ad in the category selected
		//TODO : If multiple category
        if(version_compare(JVERSION, '1.6', 'ge')) {
            if($nbcats <= 1){
                $authorisedCategory = TPermissions::getAuthorisedCategories('write');
                if(array_search(JRequest::getInt( 'category', 0 ), $authorisedCategory) === false){
                    $redirect_text = sprintf(JText::_('ADSMANAGER_FORBIDDEN_CATEGORY'),$conf->nb_ads_by_user);
                    $app->redirect(TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text ,'message');
                }
            }
        }

		//Valid account or visitor are allowed to post
		if (($user->id != 0)||($conf->submission_type == 2))
		{
			$content->userid = $user->id;
		} else {
			//trying to save ad, without being registered
			return;
		}
		$current = clone $content;

		$content->bindContent(JRequest::get( 'post' ),JRequest::get( 'files' ),
							  $conf,$this->getModel("adsmanager"),$plugins);

		if (function_exists('bindPaidSystemContent')) {
			bindPaidSystemContent($content,
								  JRequest::get( 'post' ),JRequest::get( 'files' ),
								  $conf,$this->getModel("adsmanager"));
		}

		$content->current = $current;

		$errors = $content->getErrors();
		if (count($errors) > 0) {
			$this->reloadForm($content);
		}

		if ($conf->metadata_mode == 'backendonly') {
			$content->metadata_description = strip_tags(JRequest::getVar('ad_text', ''));
			$content->metadata_keywords = str_replace(" ",",",JRequest::getVar('ad_headline', ''));
		}

		$errorMsg = null;
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('adsmanagercontent');

		try {
			$results = $dispatcher->trigger('ADSonContentBeforeSave', array ());
		} catch(Exception $e) {
			$errorMsg = $e->getMessage();
			$this->reloadForm($content,$errorMsg);
		}

		if (function_exists("getPaidSystemMode"))
			$mode = getPaidSystemMode();
		else
			$mode = "nopaidsystem";

		$total = 0;

		switch($mode) {
			case "credits":
                computeCost($total,$items,$content,$conf,$isUpdateMode);

				if ($total == 0) {
                    if ($preview == 1){
                        $content->savePending();
                        break;
                    }else{
						$content->saveContent(null);
                    }
				} else if (checkCredits($total,$user->id) == true) {
					//TODO ?
					//generateBill($content,$total,$items,$mode,"ok");

					if ($preview == 1){
                        $content->savePending();
                        break;
                    }else{
                        $transactionId = removeCredits($user->id,$total,$items,'Adsmanager');
						$content->saveContent(null);
                        linkTransactionToContent($transactionId,$content->id);
                    }
				} else {
					$errorMsg= sprintf(JText::_('PAIDSYSTEM_NOT_ENOUGH_CREDITS'), strtolower(getCurrencySymbol()));
					$this->reloadForm($content,$errorMsg);
				}
				break;

			case "payperad":
				$adid = $content->savePending();
                $content->isPending = true;
				computeCost($total,$items,$content,$conf,$isUpdateMode);

				if ($total == 0) {
					//TODO Clean Old Facture !! si on créer une annonce pyante, puis preview, puis remodification en tout gratuit
					// on arrive ici et on a une vieille facture. Puis preview puis Valid qui chercher si on a une facture. Oui
					// la vielle et donc pas BON!
					if ($preview == 1){
                        break;
                    }else{
                        $content->saveContent(null);
                    }
				} else {
					$invoice_id = generateBill($content,$items,$adid);
				}
				break;

			case "nopaidsystem":
				if ($preview == 1){
                    $content->savePending();
				break;
                }else{
                    $content->saveContent(null);
                }
				break;
		}

		if ($preview == 1) {
			$app->redirect( 'index.php?option=com_adsmanager&view=preview&id='.$content->id );
		}

		// We need to put "pending or new values" in the $content obj instead of $content->data
		$content->map();

		if (($mode == "payperad" )&&($total > 0)) {
				Invoicing::redirectToPayment($invoice_id);
		} else {

			$this->onAfterSave($conf,$contentmodel,$content,$isUpdateMode,$user);


			$this->redirectAfterSave($redirect_text,$conf,$id,JRequest::getInt( 'category', 0 ));
			}
	}

	function redirectAfterSave($redirect_text,$conf,$id,$catid)
	{
			$app = JFactory::getApplication();
			//Redirect
			if ($conf->submission_type == 2){
                if(!isset($conf->redirect_after_save)){
                    $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text,'message' );
		} else {
                    if($conf->redirect_after_save == 'custom_link'){
                        $app->redirect( TRoute::_(htmlspecialchars($conf->redirect_custom_link)));
                    } elseif($conf->redirect_after_save == 'myads') {
                        $app->redirect(TLink::getMyAdsLink(), $redirect_text ,'message');
                    } elseif($conf->redirect_after_save == 'addetails') {
                        $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&id='.$id.'&catid='.$catid), $redirect_text ,'message');
                    } else {
                        $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text,'message' );
                    }
                }
            } else {
                if(!isset($conf->redirect_after_save)){
                    $app->redirect(TLink::getMyAdsLink(), $redirect_text ,'message');
                } else {
                    if($conf->redirect_after_save == 'custom_link'){
                        $app->redirect( TRoute::_(htmlspecialchars($conf->redirect_custom_link)));
                    } elseif($conf->redirect_after_save == 'list') {
                        $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list'), $redirect_text,'message' );
                    } elseif($conf->redirect_after_save == 'addetails') {
                        $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&id='.$id.'&catid='.$catid), $redirect_text ,'message');
                    } else {
                        $app->redirect(TLink::getMyAdsLink(), $redirect_text ,'message');
                    }
                }
			}
	}

	function valid() {
		$app = JFactory::getApplication();
		$id = JRequest::getInt('id', 0);
		$user = JFactory::getUser();

        $this->addModelPath(JPATH_ADMINISTRATOR.'/components/com_adsmanager/models');
        $conf = TConf::getConfig();

        if ($conf->auto_publish == 0)
            $redirect_text = JText::_('ADSMANAGER_INSERT_SUCCESSFULL_CONFIRM');
        else
            $redirect_text = JText::_('ADSMANAGER_INSERT_SUCCESSFULL_PUBLISH');

		//TODO 3.0 pending sans Invoicing
		if(file_exists(JPATH_ROOT.'/components/com_invoicing/lib/core.php')){
            include_once(JPATH_ROOT.'/components/com_invoicing/lib/core.php');

            $db =JFactory::getDBO();

            $db->setQuery("SELECT i.invoicing_invoice_id FROM #__invoicing_invoices as i LEFT JOIN #__invoicing_users as u ON u.invoicing_user_id = i.user_id WHERE u.user_id=".$user->id." AND generator_key = '".$id."' AND status='PENDING' ORDER BY invoicing_invoice_id DESC");
            $orderid = $db->loadResult();

            if($orderid != null){
            	Invoicing::redirectToPayment($orderid);
            }
		}

		$content = JTable::getInstance('contents', 'AdsmanagerTable');

		if ($content->load($id) == false)
			return;

		//TODO
		if ($content->ad_headline != "") {
			$isUpdateMode = 0;
		} else {
			$isUpdateMode = 1;
		}

		$content->bindPending($id);
		$content->saveContent(null);

		$contentmodel	=$this->getModel( "content" );
		$this->onAfterSave($conf,$contentmodel,$content,$isUpdateMode,$user);
		$this->redirectAfterSave($redirect_text,$conf,$id,$content->catid);
    }

	function onAfterSave($conf,$contentmodel,$content,$isUpdateMode,$user) {
			if ($isUpdateMode == 0) {
				if ($conf->auto_publish == 1) {
					//$contentmodel->sendMailToUser($conf->new_subject,$conf->new_text,$user,$content,$conf,"new");
					EmailTemplateHelperEmail::sendEmail($content, 'adsmanager_new_content');
				} else if ($conf->auto_publish == 0) {
					//$contentmodel->sendMailToUser($conf->waiting_validation_subject,$conf->waiting_validation_text,$user,$content,$conf,"waiting_validation");
					EmailTemplateHelperEmail::sendEmail($content, '	adsmanager_content_waiting_validation');
					EmailTemplateHelperEmail::sendEmail($content, 'admin_adsmanager_content_waiting_validation', $conf);
				}
                /*if(($conf->email_on_waiting_validation == 1)&&($conf->auto_publish == 0)){
				if(isset($conf->admin_waiting_validation_subject) && isset($conf->admin_waiting_validation_text)){
                        $contentmodel->sendMailToAdmin($conf->admin_waiting_validation_subject,$conf->admin_waiting_validation_text,$user,$content,$conf,"admin_waiting_validation");
                    }
                }*/
				/*if ($conf->send_email_on_new == 1) {
					$contentmodel->sendMailToAdmin($conf->admin_new_subject,$conf->admin_new_text,$user,$content,$conf,"new");
				}*/
				EmailTemplateHelperEmail::sendEmail($content, 'admin_adsmanager_new_content', $conf);
			} else {
				if ($conf->update_validation == 1) {
					//$contentmodel->sendMailToUser($conf->waiting_validation_subject,$conf->waiting_validation_text,$user,$content,$conf,"waiting_validation");
					EmailTemplateHelperEmail::sendEmail($content, 'adsmanager_content_waiting_validation');
					EmailTemplateHelperEmail::sendEmail($content, 'admin_adsmanager_content_waiting_validation', $conf);
				} else {
					/*if ($conf->send_email_on_update_to_user == 1) {
						$contentmodel->sendMailToUser($conf->update_subject,$conf->update_text,$user,$content,$conf,"update");
					}*/
					EmailTemplateHelperEmail::sendEmail($content, 'adsmanager_update_content');
				}
                /*if (($conf->email_on_waiting_validation == 1)&&($conf->update_validation == 1)){
				if(isset($conf->admin_waiting_validation_subject) && isset($conf->admin_waiting_validation_text)){
                        $contentmodel->sendMailToAdmin($conf->admin_waiting_validation_subject,$conf->admin_waiting_validation_text,$user,$content,$conf,"admin_waiting_validation");
                    }
                }*/
				/*if ($conf->send_email_on_update == 1) {
					$contentmodel->sendMailToAdmin($conf->admin_update_subject,$conf->admin_update_text,$user,$content,$conf,"update");
				}*/
				EmailTemplateHelperEmail::sendEmail($content, 'admin_adsmanager_update_content', $conf);
			}

		$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('adsmanagercontent');
			try {
				$results = $dispatcher->trigger('ADSonContentAfterSave', array ($content,$isUpdateMode,$conf));
			} catch(Exception $e) {
				$errorMsg = $e->getMessage();
			}

		$cache = JFactory::getCache( 'com_adsmanager');
		$cache->clean();
	}

	function delete()
	{
		$app = JFactory::getApplication();

		$user = JFactory::getUser();

		$id = JRequest::getInt('id', 0);
		if ($id == 0) {
			$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list'));
		}

		if ($user->id == 0) {
			TTools::redirectToLogin("index.php?option=com_adsmanager&task=delete&id=".$id);
			return;
		}

		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');

		$fieldmodel	        =$this->getModel( "field" );

		$content = JTable::getInstance('contents', 'AdsmanagerTable');

		$content->load($id);
		if (($content == null)||($content->userid != $user->id))
			$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list'));

		$conf = TConf::getConfig();
		$plugins = $fieldmodel->getPlugins();

		JPluginHelper::importPlugin('adsmanagercontent');
		$dispatcher = JDispatcher::getInstance();
		try {
			$results = $dispatcher->trigger('ADSonContentBeforeDelete', array ($content,$conf));
		} catch(Exception $e) {
			$errorMsg = $e->getMessage();
		}

		$content->deleteContent($id,$conf,$plugins);

		JPluginHelper::importPlugin('adsmanagercontent');
		try {
			$results = $dispatcher->trigger('ADSonContentAfterDelete', array ($content,$conf));
		} catch(Exception $e) {
			$errorMsg = $e->getMessage();
		}

		$cache = JFactory::getCache( 'com_adsmanager');
		$cache->clean();

		$app->redirect(TLink::getMyAdsLink(), JText::_('ADSMANAGER_CONTENT_REMOVED') );
	}

	function report() {
		$app = JFactory::getApplication();
		// Check for request forgeries

		$contentid = JRequest::getInt( 'contentid',0 );
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$contentmodel =$this->getModel( "content" );
		$content = $contentmodel->getContent($contentid);
		$conf = TConf::getConfig();

		$config	= JFactory::getConfig();
		$from = JOOMLA_J3 ? $config->get('mailfrom') : $config->getValue('config.mailfrom');
		$fromname = JOOMLA_J3 ? $config->get('fromname') : $config->getValue('config.fromname');

		$email_report = JRequest::getVar('email', "" );
		$text_report = JRequest::getVar('content', "");

		$mailcontent = "Sender: $email_report<br/>";
		$mailcontent .= "Ad Owner: $content->email (userid={$content->userid})<br/>";
		$mailcontent .= "Ad id: $content->id<br/>";
		$mailcontent .= "Ad title: <a href='".JRoute::_(JUri::base().'index.php?option=com_adsmanager&view=details&id='.$content->id)."' target='_blank'>$content->ad_headline</a><br/>";
		$mailcontent .= "Message: ".htmlspecialchars($text_report);

		$subject = JText::_('ADSMANAGER_REPORT_SUBJECT');

		$return = new stdClass();

		if (!TMail::sendMail($from,$fromname,$from,$subject,$mailcontent,1))
		{
			$return->status = 'error';
		    $return->message = JText::_('ADSMANAGER_ERROR_SENDING_MAIL');
		} else {
			$return->status = 'success';
		}

		echo json_encode($return);
		exit;
	}

	function sendmessage()
	{
        $app = JFactory::getApplication();
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$contentid = JRequest::getInt( 'contentid',0 );
        $fieldname = JRequest::getString( 'fieldname','' );
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$contentmodel =$this->getModel( "content" );
		$content = $contentmodel->getContent($contentid);
		$conf = TConf::getConfig();

        if($fieldname == ''){
            $fieldMail = $content->email;
        } else {
            $fieldMail = $content->$fieldname;
        }

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('adsmanagercontent');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('adsmanagercontent');
		try {
			$results = $dispatcher->trigger('ADSonMessageBeforeSend', array ());
		} catch(Exception $e) {
			$errorMsg = $e->getMessage();
			$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=message&contentid='.$contentid), $errorMsg,'message' );
		}

		$config	= JFactory::getConfig();
		$from = JOOMLA_J3 ? $config->get('mailfrom') : $config->getValue('config.mailfrom');
		$fromname = JOOMLA_J3 ? $config->get('fromname') : $config->getValue('config.fromname');
        $sitename = JOOMLA_J3 ? $config->get('sitename') : $config->getValue('config.sitename');

		if (isset($content))
		{
            $name = JRequest::getVar('name' , "" );
			$email = JRequest::getVar('email', "" );
			jimport('joomla.mail.helper');
			if (!JMailHelper::isEmailAddress($email))
			{
				$this->setError(JText::_('INVALID_EMAIL_ADDRESS'));
				$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), 'INVALID_EMAIL_ADDRESS' ,'message');
			}
			$subject = JRequest::getVar('title', "" );
			$body = JRequest::getVar('body' , "" );
			$body = str_replace(array("\r\n", "\n", "\r"), "<br />", $body);

            if($conf->email_sender == 'website') {
                $body = sprintf(JText::_('ADSMANAGER_REPLY_TO_STRING'),$sitename,$name,$email).$body;
            }

            $files = array();
            for($i = 0; $i < $conf->number_allow_attachement; $i++){
                $file = JRequest::getVar( 'attach_file'.$i,null,'FILES');

                if ($file != null && is_uploaded_file($file['tmp_name'])) {
                    $tempPath = $config->get('tmp_path');
                    move_uploaded_file($file['tmp_name'], $tempPath.'/'.basename($file['name']));
                    $files[] = $tempPath.'/'.basename($file['name']);
                }

            }

            if(empty($files))
                $files = null;

			if ($files != null)
			{
				if(isset($conf->email_moderation) && $conf->email_moderation == 1) {
					$mailTable = JTable::getInstance('mail', 'AdsmanagerTable');
					$mail = new stdClass();
                    if($conf->email_sender == 'website') {
                        $mail->from = $from;
                        $mail->fromname = $fromname;
                    } else {
                        $mail->from = $email;
                        $mail->fromname = $name;
                    }
					$mail->recipient = $fieldMail;
					$mail->created_on = date('Y-m-d H:i:s');
					$mail->subject = $subject;
					$mail->body = $body;

					$mailTable->save($mail);

					/*$subject = sprintf(JText::_('ADSMANAGER_NEW_MODERATION_MAIL_SUBJECT'), $conf->name_admin);
					$body = JText::_('ADSMANAGER_NEW_MODERATION_MAIL_BODY');


					//TODO manage replyto, the problem is that replyto doesn't replace sender
					if (!TMail::sendMail($email,$name,$email,$subject,$body,1,NULL,NULL,$files))
					{
						$this->setError(JText::_('ADSMANAGER_ERROR_SENDING_MAIL'));
						$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_ERROR_SENDING_MAIL'),'message' );
					}*/

				} else {
					//TODO manage replyto, the problem is that replyto doesn't replace sender
                    if($conf->email_sender == 'website') {
                        $sendEmail = TMail::sendMail($from,$fromname,$fieldMail,$subject,$body,1,NULL,NULL,$files);
                    } else {
                        $sendEmail = TMail::sendMail($email,$name,$fieldMail,$subject,$body,1,NULL,NULL,$files);
                    }
					if (!$sendEmail)
					{
						$this->setError(JText::_('ADSMANAGER_ERROR_SENDING_MAIL'));
						$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_ERROR_SENDING_MAIL'),'message' );
					}
				}

                if(isset($conf->copy_to_admin) && $conf->copy_to_admin == 1){
                    $mailcontent = "Sender: $name - $email<br/>";
                    $mailcontent .= "Ad Owner: $content->email (userid={$content->userid})<br/>";
                    $mailcontent .= "Sent to: $fieldMail (It may not be the ads owner)<br/>";
                    $mailcontent .= "Ad id: $content->id<br/>";
                    $mailcontent .= "Ad title: $content->ad_headline<br/>";
                    $mailcontent .= "Message: $body";

                    if (!TMail::sendMail($from,$fromname,$from,$subject,$mailcontent,1,NULL,NULL,$filename))
                    {
                        $this->setError(JText::_('ADSMANAGER_ERROR_SENDING_MAIL'));
                        $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_ERROR_SENDING_MAIL'),'message' );
                    }
                }

                foreach($files as $file){
                    unlink($tempPath.'/'.basename($file['name']));
                }
			}
			else {
				if(isset($conf->email_moderation) && $conf->email_moderation == 1) {
					$mailTable = JTable::getInstance('mail', 'AdsmanagerTable');
					$mail = new stdClass();
					if($conf->email_sender == 'website') {
                        $mail->from = $from;
                        $mail->fromname = $fromname;
                    } else {
                        $mail->from = $email;
                        $mail->fromname = $name;
                    }
					$mail->recipient = $fieldMail;
					$mail->created_on = date('Y-m-d H:i:s');
					$mail->subject = $subject;
					$mail->body = $body;

					$mailTable->save($mail);

					/*$subject = sprintf(JText::_('ADSMANAGER_NEW_MODERATION_MAIL_SUBJECT'), $conf->name_admin);
					$body = JText::_('ADSMANAGER_NEW_MODERATION_MAIL_BODY');

					//TODO manage replyto, the problem is that replyto doesn't replace sender
					if (!TMail::sendMail($email,$name,$email,$subject,$body,1,NULL,NULL,$files))
					{
						$this->setError(JText::_('ADSMANAGER_ERROR_SENDING_MAIL'));
						$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_ERROR_SENDING_MAIL'),'message' );
					}*/

				} else {
                    if($conf->email_sender == 'website') {
                        $sendEmail = TMail::sendMail($from,$fromname,$fieldMail,$subject,$body,1);
                    } else {
                        $sendEmail = TMail::sendMail($email,$name,$fieldMail,$subject,$body,1);
                    }
					if (!$sendEmail)
					{
						$this->setError(JText::_('ADSMANAGER_ERROR_SENDING_MAIL'));
						$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_ERROR_SENDING_MAIL'),'message' );
					}
				}

				$mailcontent = "Sender: $name - $email<br/>";
				$mailcontent .= "Ad Owner: $content->email (userid={$content->userid})<br/>";
				$mailcontent .= "Sent to: $fieldMail (It may not be the ads owner)<br/>";
                $mailcontent .= "Ad id: $content->id<br/>";
				$mailcontent .= "Ad title: $content->ad_headline<br/>";
				$mailcontent .= "Message: $body";

				//Uncomment if you want a copy of all email send between users
				if(isset($conf->copy_to_admin) && $conf->copy_to_admin == 1){
                    if (!TMail::sendMail($from,$fromname,$from,$subject,$mailcontent,1))
                    {
                        $this->setError(JText::_('ADSMANAGER_ERROR_SENDING_MAIL'));
                        $app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_ERROR_SENDING_MAIL'),'message' );
                    }
                }
			}
		}

		$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=details&catid='.$content->catid.'&id='.$contentid), JText::_('ADSMANAGER_EMAIL_SENT'),'message' );
	}

	function saveprofile()
	{
		$app = JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$user  = JFactory::getUser();
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$usermodel =$this->getModel( "user" );

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('adsmanagercontent');
		try {
			$results = $dispatcher->trigger('ADSonUserBeforeSave', array ());
		} catch(Exception $e) {
			$errorMsg = $e->getMessage();
			$app->redirect( TLink::getProfileLink(), $errorMsg,'message' );
		}

		$user->orig_password = $user->password;

		$password   =  JRequest::getVar('password', "");
		$verifyPass = JRequest::getVar('verifyPass', "");
		if($password != "") {
			if($verifyPass == $password) {
				jimport('joomla.user.helper');
				$salt = JUserHelper::genRandomPassword(32);
				$crypt = JUserHelper::getCryptedPassword($password, $salt);
				$user->password = $crypt.':'.$salt;
			} else {
				$app->redirect( TLink::getProfileLink(), JText::_('_PASS_MATCH'),'message' );
				exit();
			}
		} else {
			// Restore 'original password'
			$user->password = $user->orig_password;
		}

		$user->name = JRequest::getVar('name', "");
		$user->username = JRequest::getVar('username', "");
		$user->email = JRequest::getVar('email', "");

		unset($user->orig_password); // prevent DB error!!

		if (!$user->save()) {
			$app->redirect( TLink::getProfileLink(), $user->getError() ,'message');
		}

		include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models'.DS.'field.php');
		$fieldmodel	    =$this->getModel( "field" );
		$usermodel->updateProfileFields($user->id,$fieldmodel->getPlugins());

		$app->redirect( TLink::getProfileLink(), JText::_('ADSMANAGER_PROFILE_SAVED'),'message' );
	}

	function upload() {

		header('Access-Control-Allow-Headers: Accept, Authorization, Content-Type');
		header('Access-Control-Allow-Methods: POST, GET');

		// respond to preflights
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit;
		}

		header('Content-type: text/plain; charset=UTF-8');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		jimport( 'joomla.filesystem.file' );
		jimport( 'joomla.filesystem.folder' );

		include_once(JPATH_ROOT.'/components/com_adsmanager/helpers/filter.php');

		// Settings
		$targetDir = JPATH_IMAGES_FOLDER.'/uploaded/';
		$cleanupTargetDir = false; // Remove old files
		$maxFileAge = 60 * 60; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Get parameters
		$chunk = JRequest::getInt('chunk' , 0 );
		$chunks = JRequest::getInt('chunks' , 0 );
		$fileName = JRequest::getString('name' , '' );

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		$ext = strrpos($fileName, '.');
		$fileName_b = strtolower(substr($fileName, $ext+1));
		if (!in_array($fileName_b,array("jpg","jpeg","gif","png"))) {
		         exit();
		}

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Create target dir
		if (!file_exists($targetDir))
			JFolder::create($targetDir);

		// Remove old temp files
		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// Remove temp files if they are older than the max age
				if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) {
					if(is_file($filePath)) {
                        JFile::delete($filePath);
                    }
                }
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

				if (!AdsManagerFilterInput::isSafeFile($_FILES['file'])) {
					die('Error');
				}

				// Open temp file
				$in = JFile::read($_FILES['file']['tmp_name']);
				$out = $targetDir . DIRECTORY_SEPARATOR . $fileName;
				if ($chunk != 0) {
						$content = JFile::read($out);
						$in = $content .$in ;
				}
				JFile::write($out,$in);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = $targetDir . DIRECTORY_SEPARATOR . $fileName;
			$outtmp = $out.".tmp";
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");
			if ($chunk != 0) {
					$content = JFile::read($out);
					$in = $content.$in ;
			}
			JFile::write($outtmp,$in);
			$file = array(
					$fileName,
					'',
					$outtmp,
					'',
					''
				);
			if (!AdsManagerFilterInput::isSafeFile($file)) {
				JFile::delete($outtmp);
				die('Error phpinput');
			} else {
				JFile::move($outtmp,$out);
			}
		}

		if (($fileName != "")&&(in_array($fileName_b,array('jpg','jpeg')))) {
			function image_fix_orientation($path) {
				$image = imagecreatefromjpeg($path);
				if (!$image) return;

				if(function_exists('exif_read_data')) {
					$exif = @exif_read_data($path);
					if (!empty($exif['Orientation'])) {
						switch ($exif['Orientation']) {
							case 3:
								break;
							case 6:
								break;
							case 8:
								break;
						}
						imagejpeg($image, $path);
					}
				}

				imagedestroy($image);
			}

			$path = $targetDir . DIRECTORY_SEPARATOR . $fileName;
			if (file_exists($path)) {
				image_fix_orientation($path);
			}
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id","tmpfile" : "'.$fileName.'"}');
	}

	function uploadfiles() {
		
		//header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers: Accept, Authorization, Content-Type');
		header('Access-Control-Allow-Methods: POST, GET');

		// respond to preflights
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit;
		}

		header('Content-type: text/plain; charset=UTF-8');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");

		jimport( 'joomla.filesystem.file' );
		jimport( 'joomla.filesystem.folder' );

		include_once(JPATH_ROOT.'/components/com_adsmanager/helpers/filter.php');

		// Settings
		$targetDir = JPATH_FILES_FOLDER.'/uploaded/';
		$cleanupTargetDir = false; // Remove old files
		$maxFileAge = 60 * 60; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);
		
		//load the fileupload field options
		$db = JFactory::getDbo();
		$query = "SELECT * FROM #__adsmanager_fields WHERE type='fileupload' LIMIT 0,1";
		$db->setQuery($query);
		$field = $db->loadObject();
		
		$field->options = json_decode($field->options);
		
		$extensionsAllowed = explode(',', $field->options->typeallowed);

		// Get parameters
		$chunk = JRequest::getInt('chunk' , 0 );
		$chunks = JRequest::getInt('chunks' , 0 );
		$fileName = JRequest::getString('name' , '' );

		// Clean the fileName for security reasons
		$fileName = preg_replace('/[^\w\._]+/', '', $fileName);
		$ext = strrpos($fileName, '.');
		$fileName_b = strtolower(substr($fileName, $ext+1));
		if (!in_array($fileName_b,$extensionsAllowed)) {
					exit();
		}

		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, '.');
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);

			$count = 1;
			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
				$count++;

			$fileName = $fileName_a . '_' . $count . $fileName_b;
		}

		// Create target dir
		if (!file_exists($targetDir))
			JFolder::create($targetDir);

		// Remove old temp files
		if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$filePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// Remove temp files if they are older than the max age
				if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) {
					if(is_file($filePath)) {
						JFile::delete($filePath);
					}
				}
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

				if (!AdsManagerFilterInput::isSafeFile($_FILES['file'])) {
					die('Error');
				}

				// Open temp file
				$in = JFile::read($_FILES['file']['tmp_name']);
				$out = $targetDir . DIRECTORY_SEPARATOR . $fileName;
				if ($chunk != 0) {
						$content = JFile::read($out);
						$in = $content .$in ;
				}
				JFile::write($out,$in);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = $targetDir . DIRECTORY_SEPARATOR . $fileName;
			$outtmp = $out.".tmp";
			// Read binary input stream and append it to temp file
			$in = fopen("php://input", "rb");
			if ($chunk != 0) {
					$content = JFile::read($out);
					$in = $content.$in ;
			}
			JFile::write($outtmp,$in);
			$file = array(
					$fileName,
					'',
					$outtmp,
					'',
					''
				);
			if (!AdsManagerFilterInput::isSafeFile($file)) {
				JFile::delete($outtmp);
				die('Error phpinput');
			} else {
				JFile::move($outtmp,$out);
			}
		}

		if (($fileName != "")&&(in_array($fileName_b,array('jpg','jpeg')))) {
			function image_fix_orientation($path) {
				$image = imagecreatefromjpeg($path);
				if (!$image) return;

				if(function_exists('exif_read_data')) {
					$exif = @exif_read_data($path);
					if (!empty($exif['Orientation'])) {
						switch ($exif['Orientation']) {
							case 3:
								break;
							case 6:
								break;
							case 8:
								break;
						}
						imagejpeg($image, $path);
					}
				}

				imagedestroy($image);
			}

			$path = $targetDir . DIRECTORY_SEPARATOR . $fileName;
			if (file_exists($path)) {
				image_fix_orientation($path);
			}
		}

		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id","tmpfile" : "'.$fileName.'"}');
	}

	function renew() {
		$app = JFactory::getApplication();

		$contentid = JRequest::getInt('id', 0);

		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$contentmodel =$this->getModel( "content" );

		$confmodel =$this->getModel( "configuration" );
		$conf = $confmodel->getConfiguration();

		$content = $contentmodel->getContent($contentid,false);
		if ($content == null)
			exit();

		if ($content->expiration_date == null) {
			exit();
		}

		$expiration_time = strtotime($content->expiration_date);
		$current_time = time();

		if (function_exists("renewPaidAd")) {
			renewPaidAd($contentid);
		}
		else
		{
			if ($current_time < $expiration_time - ($conf->recall_time * 3600 *24)) {
				$app->redirect(TRoute::_("index.php?option=com_adsmanager"),JText::_('ADSMANAGER_CONTENT_CANNOT_RESUBMIT'),'message');
			}
			$contentmodel->renewContent($contentid,$conf->ad_duration);
		}

		$cache = JFactory::getCache( 'com_adsmanager');
		$cache->clean();

		$app->redirect(TLink::getMyAdsLink(), JText::_('ADSMANAGER_CONTENT_RESUBMIT') ,'message');
	}

	function tags() {
		$filter = JRequest::getVar('term',"");
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
		$model =$this->getModel( "tag" );
		$tags = $model->getTags("content",$filter);
		echo json_encode($tags);
		exit();
	}
    /**
     * Check the post parameters and call the add favorite function
     *
     * @return boolean
     */
    function favorite() {
        $adId = JRequest::getInt('adId', 0);

        $user = JFactory::getUser();

        if($adId == 0){
            echo 3;
            exit();
        }
        if($user->guest){
            echo 2;
            exit();
        }

        $content = JTable::getInstance('contents', 'AdsmanagerTable');
        $content->load($adId);

        $content->favorite($user->id);
    }

    function deletefavorite(){
        $app = JFactory::getApplication();

        $user = JFactory::getUser();

		$mode = JRequest::getVar('mode', 0, 'integer');
		//If mode is set to 1, it means

		if($mode == 0){
            if($user->guest) {
                $app->redirect(Tlink::getMyFavoritesLink(),JText::_('ADSMANAGER_CONTENT_CANNOT_DELETE_FAVORITE'),'error');
            }
            $adId = JRequest::getInt('id', 0);

            if($adId == 0){
                echo 'error: Ad not selected';
                exit();
            }
		} else {
			if($user->guest) {
				echo 2;
				exit();
			}
			$adId = JRequest::getInt('adId', 0);

			if($adId == 0){
				echo 3;
				exit();
			}
		}

        $content = JTable::getInstance('contents', 'AdsmanagerTable');
        $content->load($adId);

        $content->deleteFavorite($user->id,$mode);

        $app->redirect(TLink::getMyFavoritesLink(),JText::_('ADSMANAGER_CONTENT_DELETE_FAVORITE_SUCCESS'),'message');
	}

	function deletealert() {
    	$app = JFactory::getApplication();
    	 
    	$user = JFactory::getUser();
    	 
    	if ($user->id == 0) {
    		TTools::redirectToLogin("index.php?option=com_adsmanager");
    		return;
    	}
    	 
    	$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
    	$alertmodel =$this->getModel( "alert" );
    	$fieldmodel =$this->getModel( "field" );
    	 
    	$data = new stdClass();
    	 
    	$alertid = JRequest::getInt('id',0);
    	if ($alertid != 0) {
    		$alert = $alertmodel->getAlert($alertid);
    		if (($alert == null)||($alert->userid != JFactory::getUser()->id)) {
    			echo "error";exit();
    		}
    		$alertmodel->deleteAlert($alertid);
    	} else {
    		echo "error";exit();
    	}
    	 
    	$app->redirect(TRoute::_('index.php?option=com_adsmanager&view=myalerts'), JText::_('ADSMANAGER_ALERT_DELETED') );
    }
    
    function savealert() {
    	$app = JFactory::getApplication();
    	
    	$user = JFactory::getUser();
    	
    	if ($user->id == 0) {
    		TTools::redirectToLogin("index.php?option=com_adsmanager");
    		return;
    	}
    	
    	$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models');
    	$alertmodel =$this->getModel( "alert" );
    	$fieldmodel =$this->getModel( "field" );
    	
    	$data = new stdClass();
    	
    	$alertid = JRequest::getInt('id',0);
    	if ($alertid != 0) {
    		$alert = $alertmodel->getAlert($alertid);
    		if (($alert == null)||($alert->userid != JFactory::getUser()->id)) {
    			echo "error";exit();
    		}
    		$data->id = $alert->id;
    	}
    	
    	$data->catid = JRequest::getInt('search_catid',0);
    	
    	$data->recurrence = JRequest::getString('recurrence','everyday');
    	
    	$data->userid = JFactory::getUser()->id;
    	$searchfields = $fieldmodel->getFields();
    	$filters = array();
    	$data->searchfieldssql = $fieldmodel->getSearchFieldsSql($searchfields);
    	$data->fields = json_encode(JRequest::get( 'request' ));
    	$data->tsearch = JRequest::getVar('tsearch',"");

    	$alertmodel->saveAlert($data);
    	
    	$app->redirect(TRoute::_('index.php?option=com_adsmanager&view=myalerts'), JText::_('ADSMANAGER_ALERT_SAVED') );
    }

	public function cron() {
		$conf = TConf::getConfig();

		if (($conf->crontype == "webcron")&&(JRequest::getVar('task','') == "cron")) {
			TCron::execute();
			echo "Done\n";
			exit();
		}
		if (($conf->crontype == "onrequest")&&(JRequest::getVar('task','') != "cron")) {
			TCron::execute();
			return;
		}

		echo "not allowed";
		exit();
	}

	public function rotate() {
		$app = JFactory::getApplication();
		$input = $app->input;

		$adId = $input->get('id', 0, 'integer');
		$path = $input->get('path', '', 'string');
		$src = $input->get('src', '', 'string');
		
		$currentUserId = JFactory::getUser()->id;

		$content = JTable::getInstance('contents', 'AdsmanagerTable');
		$content->load($adId);

		if($content->id === null) {
				echo 'error';
				exit;
		}
		if (($currentUserId != $content->userid)){
			exit();
		}
		
		if($path == '' || $src == '') {
			exit;
		}

		//We remove the Url from the path
		$path = str_replace(JUri::base(), '', $path);

		for($i = 0; $i < 3; $i++) {
			
			switch($i) {
				case 0: $suffix = '';
						break;
				case 1: $suffix = '_m';
						break;
				case 2: $suffix = '_t';
						break;
			}

			$fileType = substr($src, strrpos($src, '.') + 1);
			$filename = basename($src, '.'.$fileType);

			$image = $filename.$suffix.'.'.$fileType;
			$newImage = '1'.$filename.$suffix.'.'.$fileType;

			$path = str_replace($src, '', $path);
			
			$rotateFileName = JPATH_ROOT."/".$path.$image;
			$newFileName = JPATH_ROOT."/".$path.$newImage;
			
			// File and rotation
			$degrees = -90;
			$fileType = strtolower(substr($image, strrpos($image, '.') + 1));
			if($fileType == 'png'){
				$source = imagecreatefrompng($rotateFileName);
				$bgColor = imagecolorallocatealpha($source, 255, 255, 255, 127);
				// Rotate
				$rotate = imagerotate($source, $degrees, $bgColor);
				imagesavealpha($rotate, true);
				imagepng($rotate,$newFileName);
			}

			if($fileType == 'jpg' || $fileType == 'jpeg'){
				$source = imagecreatefromjpeg($rotateFileName);
				// Rotate
				$rotate = imagerotate($source, $degrees, 0);
				imagejpeg($rotate,$newFileName);
			}

			// Free the memory
			imagedestroy($source);
			imagedestroy($rotate);
			
			/*foreach($gallery as $key => $galleryImage) {
				if($src == $galleryImage->large || "1".$src == $galleryImage->large) {
					switch($i) {
						case 0: $gallery[$key]->large = "1".$src;
								break;
						case 1: $gallery[$key]->medium = "m_1".$src;
								break;
						case 2: $gallery[$key]->thumbnail = "t_1".$src;
								break;
					}
					$returnImage = $gallery[$key]->large;
				}
			}*/

            JFile::delete($rotateFileName);
            JFile::move($newFileName,$rotateFileName);
		}

        $updateTime = filemtime(JPATH_ROOT."/".$path.$src);
		
		/*$post=array();
		$post["field_Galerie"] = json_encode($gallery);
		$post["userprofile_user_id"]=$userId;
		$post = (object) $post;
		$db = JFactory::getDbo();
		$db->updateObject('#__userprofile_users',$post,'userprofile_user_id');*/

		echo JUri::base()."/".$path.$src.'?'.$updateTime;exit();
	}

    public function restoread()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$db = JFactory::getDbo();

		$id = $input->getInt('id');

		// Skontroluj, či inzerát existuje a zisti aktuálnu kategóriu + old_catid
		$query = $db->getQuery(true)
			->select('a.id, ac.catid, ac.old_catid')
			->from('#__adsmanager_ads AS a')
			->join('LEFT', '#__adsmanager_adcat AS ac ON a.id = ac.adid')
			->where('a.id = ' . (int)$id);
		$db->setQuery($query);
		$ad = $db->loadObject();

		if (!$ad) {
			$app->enqueueMessage(JText::_('COM_ADSMANAGER_RESTORE_NOT_FOUND'), 'error');
			$app->redirect(JURI::root());
			return;
		}

		// Ak už inzerát NIE JE v archíve (teda catid != 39), znamená to, že už bol obnovený
		if ((int)$ad->catid !== 39) {
			$app->enqueueMessage(JText::_('COM_ADSMANAGER_RESTORE_ALREADY'), 'message');
			$app->redirect(JRoute::_('index.php?option=com_adsmanager&view=details&id='.(int)$id.'&catid='.(int)$ad->catid));
			return;
		}

		// Obnov inzerát do pôvodnej kategórie
		$updateQuery = $db->getQuery(true)
			->update('#__adsmanager_adcat')
			->set('catid = ' . (int)$ad->old_catid)
			->where('adid = ' . (int)$id);
		$db->setQuery($updateQuery);
		$db->execute();

		// Načítaj počet dní z konfigurácie
		$query2 = $db->getQuery(true)
			->select($db->quoteName('ad_duration'))
			->from($db->quoteName('#__adsmanager_config'))
			->where('id = 1');
		$db->setQuery($query2);
		$adDuration = (int) $db->loadResult();

		// Nastav expiráciu a publication_date a resetuj views
		$newDate = JFactory::getDate()->modify('+' . $adDuration . ' days')->toSql();
		$today = JFactory::getDate()->toSql();

		$updateQuery2 = $db->getQuery(true)
			->update('#__adsmanager_ads')
			->set('expiration_date = ' . $db->quote($newDate))
			->set('publication_date = ' . $db->quote($today))
			->set('date_created = ' . $db->quote($today))
			->set('views = 0')
			->where('id = ' . (int)$id);
		$db->setQuery($updateQuery2);
		$db->execute();

		// Načítaj aktuálnu kategóriu po obnovení (už mimo archívu)
		$query3 = $db->getQuery(true)
			->select('catid')
			->from('#__adsmanager_adcat')
			->where('adid = ' . (int)$id);
		$db->setQuery($query3);
		$currentCatid = (int) $db->loadResult();

		$app->enqueueMessage(JText::_('COM_ADSMANAGER_RESTORE_SUCCESS'), 'message');
		$app->redirect(JRoute::_('index.php?option=com_adsmanager&view=details&id='.(int)$id.'&catid='.(int)$currentCatid));
	}

	public function makepremium()
	{
		$app = JFactory::getApplication();
		$contentid = JRequest::getInt('id', 0);

		// Pridáme cestu k admin modelom
		$this->addModelPath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_adsmanager' . DS . 'models');

		// Načítame model content
		$contentmodel = $this->getModel('content');
		if (!$contentmodel) {
			$app->enqueueMessage(JText::_('COM_ADSMANAGER_INVALID_AD_MODEL'), 'error');
			$app->redirect('index.php?option=com_adsmanager&view=myads');
			return;
		}

		// Získame inzerát
		$content = $contentmodel->getContent($contentid, false);
		if ($content == null) {
			$app->enqueueMessage(JText::_('COM_ADSMANAGER_INVALID_AD_CONTENT'), 'error');
			$app->redirect('index.php?option=com_adsmanager&view=myads');
			return;
		}

		// Kontrola vlastníka
		$user = JFactory::getUser();
		if ($content->userid != $user->id) {
			$app->enqueueMessage(JText::_('COM_ADSMANAGER_INVALID_AD_NOT_THIS_USER'), 'error');
			$app->redirect('index.php?option=com_adsmanager&view=myads');
			return;
		}

		// Pripravíme view pre makepremium
		$view = $this->getView('makepremium', 'html');
		$view->assignRef('content', $content); // tu priradíme content do view
		$view->display();
	}

	public function sendpremiumrequest()
	{
		// Kontrola tokenu
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app  = JFactory::getApplication();
		$data = $app->input->getArray($_POST);

		$adid    = (int)$data['adid'];
		$userid  = (int)$data['userid'];
		$comment = isset($data['user_comment']) ? $data['user_comment'] : '';
		$price   = isset($data['price']) ? (float)$data['price'] : 0;

		$headline    = isset($data['headline']) ? $data['headline'] : '';
		$description = isset($data['description']) ? $data['description'] : '';
		$url         = JRoute::_('index.php?option=com_adsmanager&view=details&id='.$adid.'&catid=0', false);

		// Správny úplný odkaz
		$adLink = JUri::base() . ltrim($url, '/');

		$db = JFactory::getDbo();

		// Predzápis do tabuľky
		$columns = ['adid','userid','headline','description','url','custom_html','active_from','published','price'];
		$values  = [
			(int)$adid,
			(int)$userid,
			$db->quote($headline),
			$db->quote($description),
			$db->quote($adLink),
			$db->quote($comment),
			$db->quote(date('Y-m-d H:i:s')),
			0,
			$price
		];

		$query = $db->getQuery(true)
			->insert($db->quoteName('#__adsmanager_premium_ads'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));

		$db->setQuery($query);
		$db->execute();

		// Email pre admina
		$mailer = JFactory::getMailer();
		$config = JFactory::getConfig();
		$adminEmail = $config->get('mailfrom');
		$siteName   = $config->get('sitename');

		$subject = $siteName . ": premium ad request";
		$body    = "User ID: $userid requested premium for ad ID: $adid\n";
		$body   .= "Ad link: $adLink\n";
		$body   .= "Headline: $headline\n";
		$body   .= "Description: $description\n";
		$body   .= "Price: $price €\n";
		$body   .= "User comment: $comment\n";

		$mailer->sendMail($config->get('mailfrom'), $config->get('fromname'), $adminEmail, $subject, $body);

		$app->enqueueMessage(JText::_('COM_ADSMANAGER_PREMIUM_REQUEST_SENT'));
		$app->redirect('index.php?option=com_adsmanager&view=myads');
	}

}
