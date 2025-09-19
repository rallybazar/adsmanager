<?php
defined('_JEXEC') or die('Restricted access');

JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'tables');
jimport('joomla.application.component.controller');

class AdsmanagerControllerPremiumads extends TController
{
    function __construct($config = array())
    {
        parent::__construct($config);

        // Register tasks
        $this->registerTask('apply', 'save');
        $this->registerTask('save2new', 'save');
    }

    function init()
    {
        $this->_view = $this->getView('admin', 'html');
        $this->_model = $this->getModel('premiumads');
        if (!JError::isError($this->_model)) {
            $this->_view->setModel($this->_model, true);
        }
        $confmodel = $this->getModel('configuration');
        if (!JError::isError($confmodel)) {
            $this->_view->setModel($confmodel);
        }
    }

    function display($cachable = false, $urlparams = false)
    {
        $this->init();

        $confmodel = $this->getModel("configuration");
        $this->_view->setModel($confmodel);

        $this->_view->setLayout("listpremiumads"); // explicitne nový layout
        $this->_view->display();
    }

    function edit()
    {
        $this->init();

        $confmodel = $this->getModel("configuration");
        $this->_view->setModel($confmodel);

        $this->_view->setLayout("editpremiumads"); // explicitne nový edit layout
        $this->_view->display();
    }

    function add()
    {
        $this->init();

        $confmodel = $this->getModel("configuration");
        $this->_view->setModel($confmodel);

        $this->_view->setLayout("editpremiumads"); // používa rovnaký edit layout
        $this->_view->display();
    }

    function save()
    {
        $app = JFactory::getApplication();
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');
        $id = JFactory::getApplication()->input->getInt('id', 0);

        $table = JTable::getInstance('Premiumad', 'AdsmanagerTable');

        if ($id) {
            $table->load($id);
        }

        if (!$table->bind($data)) {
            $app->enqueueMessage($table->getError(), 'error');
            $this->setRedirect('index.php?option=com_adsmanager&c=premiumads');
            return;
        }

        if (!$table->check()) {
            $app->enqueueMessage($table->getError(), 'error');
            $this->setRedirect('index.php?option=com_adsmanager&c=premiumads');
            return;
        }

        if (!$table->store()) {
            $app->enqueueMessage($table->getError(), 'error');
            $this->setRedirect('index.php?option=com_adsmanager&c=premiumads');
            return;
        }

        $app->enqueueMessage(JText::_('COM_ADSMANAGER_PREMIUM_AD_SAVED'), 'message');

        $task = $this->getTask();
        switch ($task) {
            case 'apply':
                $this->setRedirect('index.php?option=com_adsmanager&c=premiumads&task=edit&id=' . $table->id);
                break;
            case 'save2new':
                $this->setRedirect('index.php?option=com_adsmanager&c=premiumads&task=add');
                break;
            default:
                $this->setRedirect('index.php?option=com_adsmanager&c=premiumads');
                break;
        }
    }

    function remove()
    {
        $app = JFactory::getApplication();
        $ids = JFactory::getApplication()->input->get('cid', array(), 'array');
        $table = JTable::getInstance('Premiumad', 'AdsmanagerTable');

        foreach ($ids as $id) {
            $table->delete($id);
        }

        $app->enqueueMessage(JText::_('COM_ADSMANAGER_PREMIUM_AD_REMOVED'), 'message');
        $this->setRedirect('index.php?option=com_adsmanager&c=premiumads');
    }
}
