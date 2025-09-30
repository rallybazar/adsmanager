<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class AdsmanagerViewMakepremium extends TView
{
    protected $content;

    public function display($tpl = null)
    {
        $app = JFactory::getApplication();

        // content priamo priradený z controllera
        if ($this->content == null) {
            $app->enqueueMessage(JText::_('COM_ADSMANAGER_INVALID_AD_CONTENT_NULL'), 'error');
            $app->redirect('index.php?option=com_adsmanager&view=myads');
            return;
        }

        parent::display($tpl);
    }
}

