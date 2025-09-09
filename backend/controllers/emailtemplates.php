<?php
/**
 * @author 		Anthony Verdure 
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper as JArrayHelper;

JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_adsmanager' . DS . 'tables');
jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class AdsmanagerControllerEmailtemplates extends TController
{
	var $_view = null;
	var $_model = null;

	function __construct($config = [])
	{
		parent::__construct($config);

		// Apply, Save & New
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
	}

	function init()
	{
		// Set the default view name from the Request
		$this->_view = $this->getView("admin", 'html');

		// Push a model into the view
		$this->_model = $this->getModel("emailtemplate");
		try {
			$this->_view->setModel($this->_model, true);
		} catch (Exception $e) {
		}

		$confmodel = $this->getModel("configuration");
		$this->_view->setModel($confmodel);
	}

	function display($cachable = false, $urlparams = false)
	{
		$this->init();
		$this->_view->setLayout("emailtemplates");
		$this->_view->display();
	}

	function edit()
	{
		$this->init();
		$this->_view->setLayout("emailtemplate");
		$this->_view->display();
	}

	function add()
	{
		$this->init();
		$this->_view->setLayout("emailtemplate");
		$this->_view->display();
	}

	function save()
	{
		$app = JFactory::getApplication();

		$this->canAccess();

		$emailTemplate = JTable::getInstance('emailtemplate', 'AdsmanagerTable');
		$post = JFactory::getApplication()->input->post->getArray();
		// Needed to accept the HTML tags
		// We load the body without any filter, the access to that page must be reserved to people you trust.
		$bodyRaw = JFactory::getApplication()->input->get('body', '', 'RAW');
		$post['body'] = $bodyRaw;
		
		$post['published'] = $post['emailTemplatePublished'];

		if ($post['id'] !== "") {
			$post['modified_on'] = date("Y-m-d H:i:s");
			$post['modified_by'] = JFactory::getUser()->id;
		} else {
			$post['created_by'] = JFactory::getUser()->id;
		}


		// bind it to the table
		if (!$emailTemplate->bind($post)) {
			throw new Exception($emailTemplate->getError());
		}
		// store it in the db
		if (!$emailTemplate->store()) {
			throw new Exception($emailTemplate->getError());
		}

		cleanAdsManagerCache();
		$post = [];

		// Redirect the user and adjust session state based on the chosen task.
		$task = JFactory::getApplication()->input->getCmd('task');
		$app->enqueueMessage(\JText::_('ADSMANAGER_EMAIL_TEMPLATES_SAVED'), 'message');
		switch ($task) {
			case 'apply':
				$app->redirect('index.php?option=com_adsmanager&c=emailtemplates&task=edit&id=' . $emailTemplate->id, 200);
				break;

			case 'save2new':
				$app->redirect('index.php?option=com_adsmanager&c=emailtemplates&task=add', 200);
				break;

			default:
				$app->redirect('index.php?option=com_adsmanager&c=emailtemplates', 200);
				break;
		}
	}

	function remove()
	{
		$app = JFactory::getApplication();

		$this->canAccess();

		$emailTemplate = JTable::getInstance('emailtemplate', 'AdsmanagerTable');

		$ids = JFactory::getApplication()->input->get('cid', array(0));
		if (!is_array($ids)) {
			$table = array();
			$table[0] = $ids;
			$ids = $table;
		}

		foreach ($ids as $id) {

			$emailTemplate->delete($id);
		}

		cleanAdsManagerCache();

		$app->enqueueMessage(\JText::_('ADSMANAGER_EMAIL_TEMPLATES_REMOVED'), 'message');
		$app->redirect('index.php?option=com_adsmanager&c=emailtemplates', 200);
	}

	function unpublish()
	{
		$this->canAccess();
		$this->_changeState();
	}

	function publish()
	{
		$this->canAccess();
		$this->_changeState();
	}

	function _changeState()
	{
		$this->canAccess();
		$app = JFactory::getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit('Invalid Token');

		$cid = JFactory::getApplication()->input->get('cid', array(), '', 'array');
		$publish = ($this->getTask() == 'publish' ? 1 : 0);

		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			$action = $publish ? 'publish' : 'unpublish';
			throw new Exception(JText::_('Select an item to' . $action, true));
		}

		$model = $this->getModel("adsmanager");
		$model->changeState("#__adsmanager_emailtemplates", "id", "published", $publish, $cid);

		cleanAdsManagerCache();

		$app->redirect('index.php?option=com_adsmanager&c=emailtemplates', 200);
	}

	private function canAccess()
	{
		$app = JFactory::getApplication();

		//check if the user can access the tempaltes
		$user = JFactory::getUser();
		if (!$user->authorise('adsmanager.accessemailtemplates', 'com_adsmanager')) {
			$app->enqueueMessage(\JText::_('ADSMANAGER_ORDERING_SAVED'), 'error');
			$app->redirect('index.php', 200);
		}

		return true;
	}
}
