<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class AdsmanagerControllerPremiumads extends TController
{
    protected $_view = null;
    protected $_model = null;

    function __construct($config = array())
    {
        parent::__construct($config);
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

        $baseurl = JURI::base() . "../";
        $this->_view->assign("baseurl", $baseurl);
        $this->_view->assignRef("baseurl", $baseurl);
    }

    function display($cachable = false, $urlparams = false)
    {
        $this->init();
        $this->_view->setLayout('premiumads');
        $this->_view->display();
    }

    function add()
    {
        $this->init();
        $app = JFactory::getApplication();
        $id = $app->input->getInt('id', 0);

        $model = $this->getModel('premiumads');

        if ($id) {
            $ad = $model->getItem($id);
            if (!$ad) {
                $app->enqueueMessage(JText::_('COM_ADSMANAGER_PREMIUM_AD_NOT_FOUND'), 'error');
                $app->redirect(JRoute::_('index.php?option=com_adsmanager&c=premiumads', false));
                return;
            }
        } else {
            // Nový objekt
            $ad = new stdClass();
            $ad->id = 0;
            $ad->adid = null;
            $ad->userid = null;
            $ad->headline = '';
            $ad->description = '';
            $ad->image = '';
            $ad->url = '';
            $ad->custom_html = '';
            $ad->priority = 0;
            $ad->active_from = null;
            $ad->active_to = null;
            $ad->published = 1;
            $ad->views = 0;
            $ad->date_created = JFactory::getDate()->toSql();
            $ad->date_modified = null;
        }

        $this->_view->setLayout('editpremiumad');
        $this->_view->assignRef('content', $ad);
        $this->_view->display();
    }

    function edit()
    {
        $this->init();
        $app = JFactory::getApplication();
        $id = $app->input->getInt('id', 0);

        $model = $this->getModel('premiumads');

        if ($id) {
            $ad = $model->getItem($id);
            if (!$ad) {
                $app->enqueueMessage(JText::_('COM_ADSMANAGER_PREMIUM_AD_NOT_FOUND'), 'error');
                $app->redirect(JRoute::_('index.php?option=com_adsmanager&c=premiumads', false));
                return;
            }
        } else {
            // Nový objekt
            $ad = new stdClass();
            $ad->id = 0;
            $ad->adid = null;
            $ad->userid = null;
            $ad->headline = '';
            $ad->description = '';
            $ad->image = '';
            $ad->url = '';
            $ad->custom_html = '';
            $ad->priority = 0;
            $ad->active_from = null;
            $ad->active_to = null;
            $ad->published = 1;
            $ad->views = 0;
            $ad->date_created = JFactory::getDate()->toSql();
            $ad->date_modified = null;
        }

        $this->_view->setLayout('editpremiumad');
        $this->_view->assignRef('content', $ad);
        $this->_view->display();
    }

    function save()
    {
        $this->init();
        $app = JFactory::getApplication();

        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $data = $app->input->post->getArray();
        $files = $app->input->files->get('jform', array(), 'array'); // predpokladáme form field "jform[image]"

        $ad = JTable::getInstance('premiumads', 'AdsmanagerTable');

        $id = isset($data['id']) ? (int)$data['id'] : 0;
        if ($id) {
            $ad->load($id);
        }

        if (!$ad->bindContent($data, $files)) {
            JError::raiseWarning(500, JText::_('COM_ADSMANAGER_ERROR_BINDING'));
            return;
        }

        if (!$ad->saveContent()) {
            JError::raiseWarning(500, JText::_('COM_ADSMANAGER_ERROR_SAVING'));
            return;
        }

        $task = $app->input->getCmd('task');
        switch ($task) {
            case 'apply':
                $app->redirect('index.php?option=com_adsmanager&c=premiumads&task=edit&id=' . $ad->id, JText::_('COM_ADSMANAGER_PREMIUM_AD_SAVED'), 'message');
                break;
            case 'save2new':
                $app->redirect('index.php?option=com_adsmanager&c=premiumads&task=add', JText::_('COM_ADSMANAGER_PREMIUM_AD_SAVED'), 'message');
                break;
            default:
                $app->redirect('index.php?option=com_adsmanager&c=premiumads', JText::_('COM_ADSMANAGER_PREMIUM_AD_SAVED'), 'message');
                break;
        }
    }

    function remove()
    {
        $this->canAccess();
        $app = JFactory::getApplication();

        $ad = JTable::getInstance('premiumads', 'AdsmanagerTable');
        $ids = JRequest::getVar('cid', array(0), '', 'array');

        foreach ($ids as $id) {
            $ad->delete($id);
        }

        $app->redirect('index.php?option=com_adsmanager&c=premiumads', JText::_('COM_ADSMANAGER_PREMIUM_AD_REMOVED'), 'message');
    }

    public function delete()
    {
        // Získame ID zvolených inzerátov
        $cid = $this->input->post->get('cid', array(), 'array');

        if(!empty($cid)) {
            $model = $this->getModel('Premiumads');

            foreach($cid as $id) {
                $model->deleteItem((int)$id);
            }

            $this->setMessage(JText::plural('COM_ADSMANAGER_N_ITEMS_DELETED', count($cid)));
        } else {
            $this->setMessage(JText::_('COM_ADSMANAGER_NO_ITEM_SELECTED'), 'warning');
        }

        // Presmerovanie späť na zoznam
        $this->setRedirect(JRoute::_('index.php?option=com_adsmanager&c=premiumads', false));
    }

    function publish() { $this->_changeState(1); }
    function unpublish() { $this->_changeState(0); }

    protected function _changeState($publish)
    {
        $this->canAccess();
        $app = JFactory::getApplication();

        $cid = JRequest::getVar('cid', array(), '', 'array');
        JArrayHelper::toInteger($cid);

        if (count($cid) < 1) {
            JError::raiseError(500, JText::_('Select an item to change state'));
        }

        $model = $this->getModel("premiumads");
        $model->changeState("#__adsmanager_premium_ads","id","published",$publish,$cid);

        $app->redirect('index.php?option=com_adsmanager&c=premiumads');
    }

    private function canAccess()
    {
        $user = JFactory::getUser();
        if (!$user->authorise('adsmanager.accesscontent', 'com_adsmanager')) {
            return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
        }
        return true;
    }
}
