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
class AdsmanagerControllerMails extends TController
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
		$this->_model = $this->getModel("mail");
		if (!JError::isError( $this->_model )) {
			$this->_view->setModel( $this->_model, true );
		}
		
		$confmodel	  = $this->getModel("configuration");
		$this->_view->setModel( $confmodel );
	}
	
	function display($cachable = false, $urlparams = false)
	{
		$this->init();
		$this->_view->setLayout("listmails");
		$this->_view->display();
	}
	
	function edit()
	{
		$this->init();
		$this->_view->setLayout("editmail");
		$this->_view->display();
	}
	
	function add()
	{
		$this->init();
		$this->_view->setLayout("editmail");
		$this->_view->display();
	}
	
	function save()
	{
		$this->canAccess();
		$app = JFactory::getApplication();
		
		$mail = JTable::getInstance('mail', 'AdsmanagerTable');
        
        $post = JRequest::get( 'post',JREQUEST_ALLOWHTML );
        
		// bind it to the table
		if (!$mail -> bind($post)) {
			return JError::raiseWarning( 500, $mail->getError() );
		}
		// store it in the db
		if (!$mail -> store()) {
			return JError::raiseWarning( 500, $mail->getError() );
		}	
		
		$model = $this->getModel("configuration");
		$conf = $model->getConfiguration();
		
		cleanAdsManagerCache();
	
		// Redirect the user and adjust session state based on the chosen task.
		$task = JRequest::getCmd('task');
		switch ($task)
		{
			case 'apply':
				$app->redirect( 'index.php?option=com_adsmanager&c=mails&task=edit&id='.$mail->id, JText::_('ADSMANAGER_MAIL_SAVED'),'message' );
				break;
		
			case 'save2new':
				$app->redirect( 'index.php?option=com_adsmanager&c=mails&task=add', JText::_('ADSMANAGER_MAIL_SAVED'),'message' );
				break;
		
			default:
				$app->redirect( 'index.php?option=com_adsmanager&c=mails', JText::_('ADSMANAGER_MAIL_SAVED'),'message' );
			break;
		}
		
	}
	
	function remove()
	{
		$this->canAccess();
		$app = JFactory::getApplication();

		
		$mail = JTable::getInstance('mail', 'AdsmanagerTable');
		
		$ids = JRequest::getVar( 'cid', array(0));
		if (!is_array($ids)) {
			$table = array();
			$table[0] = $ids;
			$ids = $table;
		}
		
		foreach($ids as $id){
			$mail->deleteContent($id);
		}
		
		cleanAdsManagerCache();
		
		$app->redirect( 'index.php?option=com_adsmanager&c=mails', JText::_('ADSMANAGER_MAIL_REMOVED'),'message' );
	}
	
    function send() {
		$this->canAccess();
        $id = JRequest::getInt( 'id', 0);
		
		$app = JFactory::getApplication();

        if($this->send_email($id) === true) {
            $app->redirect('index.php?option=com_adsmanager&c=mails', JText::_('ADSMANAGER_MAIL_SENT'), 'message');
        } else {
            switch($errorType) {
                case 'nid' : $app->redirect('index.php?option=com_adsmanager&c=mails', JText::_('ADSMANAGER_MAIL_SEND_NO_MAIL'), 'error');
                             break;
                case 'binderror' : return JError::raiseWarning( 500, $mailTable->getError() );
                                   break;
                case 'storeerror' : return JError::raiseWarning( 500, $mailTable->getError() );
                                    break;
            }
        }
        
        $app->redirect(JRoute::_('index.php?option=com_adsmanager&c=mails'), JText::_('ADSMANAGER_MAIL_SEND_NO_MAIL'), 'error');
    }
    
    function send_email($id) {
		$this->canAccess();
        $app = JFactory::getApplication();
        
        if(!$id)
            return 'nid';
        
        $model = $this->getModel("mail");
        $mail = $model->getMail($id);
        
        if($mail->statut == 1) {
			return 'mailalreadysent';
		}
        
        if (version_compare(JVERSION,'2.5.0','>=')) {
            // Get a JMail instance
            $mailer = JFactory::getMailer();
            //$mail->sendMail("support@juloa.com", "support@juloa.com", "support@juloa.com","je fais un test", "je fais un test", 1);
            $mailer->sendMail($mail->from, $mail->fromname, $mail->recipient, $mail->subject, $mail->body, 1, null, null, null, null, null);
        } else {
            JUtility::sendMail($mail->from, $mail->fromname, $mail->recipient, $mail->subject, $mail->body, 1, null, null, null, null, null);
        }
        
        $mailTable = JTable::getInstance('mail', 'AdsmanagerTable');
        $mail->statut = 1;
        
        if (!$mailTable -> bind($mail)) {
			return 'binderror';
		}
		// store it in the db
		if (!$mailTable -> store()) {
			return ;
		}
        
        return true;
    }
    
    function send_emails() {
		$this->canAccess();
        $app = JFactory::getApplication();

		$mail = JTable::getInstance('mail', 'AdsmanagerTable');
		
		$ids = JRequest::getVar( 'cid', array(0));
		if (!is_array($ids)) {
			$table = array();
			$table[0] = $ids;
			$ids = $table;
		}
		
		foreach($ids as $id){
			if($this->send_email($id) !== true) {
                switch($errorType) {
                    case 'nid' : $app->redirect('index.php?option=com_adsmanager&c=mails', JText::_('ADSMANAGER_MAIL_SEND_NO_MAIL'), 'error');
                                 break;
                    case 'binderror' : return JError::raiseWarning( 500, $mailTable->getError() );
                                       break;
                    case 'storeerror' : return JError::raiseWarning( 500, $mailTable->getError() );
                                        break;
                }
            }
		}
		
		$app->redirect( JRoute::_('index.php?option=com_adsmanager&c=mails'), JText::_('ADSMANAGER_MAILS_SENT'),'message' );
	}
	
	private function canAccess() {
		//check if the user can access the moderation emails
		$user = JFactory::getUser();
		if(!$user->authorise('adsmanager.accessmail','com_adsmanager')) {
			return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		return true;
	}
}
