<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class AdsmanagerControllerPremiumads extends TController
{
    var $_view = null;
    var $_model = null;

    function __construct($config = array())
    {
        parent::__construct($config);
    }

    function init()
    {
        // Používame view "admin" – AdsManager má zvyčajne spoločný admin view
        $this->_view = $this->getView('admin', 'html');

        // Model Premium Ads
        $this->_model = $this->getModel('premiumads');
        if (!JError::isError($this->_model)) {
            $this->_view->setModel($this->_model, true);
        }

        // Model Configuration
        $confmodel = $this->getModel('configuration');
        if (!JError::isError($confmodel)) {
            $this->_view->setModel($confmodel);
        }

        // Explicitne nastavíme layout
        $this->_view->setLayout('premiumads');
    }

    function display($cachable = false, $urlparams = false)
    {
        $this->init();

        // Zavoláme view metódu pre načítanie záznamov
        if (method_exists($this->_view, '_premiumads')) {
            $this->_view->_premiumads();
        }

        // Zobrazíme view
        $this->_view->display();
    }

    function edit()
    {
        $this->init();
        $id = JFactory::getApplication()->input->getInt('id', 0);

        // Povedz view, že má použiť layout "editpremiumad"
        $this->_view->setLayout('editpremiumad');
        $this->_view->assign('adId', $id);

        // Zobrazí layout
        $this->_view->display();
    }


    function save()
    {
        $this->init();
        $app = JFactory::getApplication();
        $data = $app->input->getArray($_POST);

        $model = $this->_model;
        if ($model->saveData($data)) {
            $app->enqueueMessage(JText::_('COM_ADSMANAGER_PREMIUM_AD_SAVED'));
        } else {
            $app->enqueueMessage(JText::_('COM_ADSMANAGER_ERROR_SAVING_PREMIUM_AD'), 'error');
        }

        $app = JFactory::getApplication();
        $app->redirect(
            JRoute::_('index.php?option=com_adsmanager&c=premiumads&task=display', false),
            JText::_('COM_ADSMANAGER_PREMIUM_AD_SAVED')
        );

    }
}
