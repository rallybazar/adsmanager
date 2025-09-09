<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class AdsmanagerControllerImportad extends TController
{
    var $_view = null;
    var $_model = null;

    function __construct($config = array())
    {
        parent::__construct($config);
    }

    function init()
    {
        $this->_view = $this->getView('admin', 'html');
        $this->_model = $this->getModel('importad');
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
        $this->_view->setLayout('importad');
        $this->_view->display();
    }

    /**
     * Spojená logika – spracuje JSON a rovno uloží draft
     */
    public function createDraft()
    {
        $app = JFactory::getApplication();

        if (!JSession::checkToken()) {
            $app->redirect(
                JRoute::_('index.php?option=com_adsmanager&c=importad&task=display', false),
                JText::_('COM_ADSMANAGER_IMPORTAD_INVALID_TOKEN'),
                'error'
            );
            return;
        }

        // Načítame JSON z POST
        $jsontext = $app->input->getString('jsontext', '');
        if (empty($jsontext)) {
            $app->redirect(
                JRoute::_('index.php?option=com_adsmanager&c=importad&task=display', false),
                JText::_('COM_ADSMANAGER_IMPORTAD_NO_JSON'),
                'error'
            );
            return;
        }

        // Pokúsime sa dekódovať JSON
        $data = json_decode($jsontext, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $app->setUserState('com_adsmanager.import_data_raw', $jsontext);
            $app->redirect(
                JRoute::_('index.php?option=com_adsmanager&c=importad&task=display', false),
                JText::_('COM_ADSMANAGER_IMPORTAD_INVALID_JSON'),
                'error'
            );
            return;
        }

        // Uložíme dáta
        $app->setUserState('com_adsmanager.import_data', $data);

        $model = $this->getModel('importad');
        try {
            $model->saveDraft($data);
            $model->setImportData(null);

            // Po úspechu vynulujeme textarea – nastavíme defaultný JSON
            $defaultJson = json_encode([
                "category" => "",
                "name" => "",
                "ad_city" => "",
                "ad_headline" => "",
                "ad_text" => "",
                "ad_price" => "",
                "ad_engine" => "",
                "email" => "",
                "ad_phone" => "",
                "images" => [""]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $app->setUserState('com_adsmanager.import_data_raw', $defaultJson);

            // Presmerujeme späť na stránku s formulárom (nie do zoznamu)
            $app->redirect(
                JRoute::_('index.php?option=com_adsmanager&c=importad&task=display', false),
                JText::_('COM_ADSMANAGER_IMPORTAD_DRAFT_CREATED'),
                'message'
            );
        } catch (Exception $e) {
            $app->redirect(
                JRoute::_('index.php?option=com_adsmanager&c=importad&task=display', false),
                JText::_('COM_ADSMANAGER_IMPORTAD_SAVE_FAILED') . ': ' . $e->getMessage(),
                'error'
            );
        }
    }

    public function purgeExpired()
    {
        // Zavoláme model
        $model = $this->getModel('importad');
        $count = $model->purgeExpired();

        // Správa pre používateľa
        JFactory::getApplication()->enqueueMessage($count . ' ' . JText::_('COM_ADSMANAGER_IMPORTAD_ARCHIVED_SUCCESS'));
        $this->setRedirect(JRoute::_('index.php?option=com_adsmanager&c=importad', false));
    }

}
