<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

class AdsmanagerModelPremiumads extends TModel
{
    public function getItem($pk = null)
    {
        // Ak nie je zadané ID, načítaj z inputu
        if ($pk === null) {
            $pk = JFactory::getApplication()->input->getInt('id');
        }

        // Získaj JTable objekt
        $table = $this->getTable();

        if ($table->load($pk)) {
            return $table; // úspešne načítané, vrátime objekt tabuľky
        }

        return false; // záznam sa nenašiel
    }

    public function getTable($type = 'Premiumad', $prefix = 'AdsmanagerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        // Form v tomto štýle zatiaľ nepoužijeme, budeme renderovať PHP view
        return false;
    }

    protected function loadFormData()
    {
        // Tu by sme načítali údaje do edit view
        return $this->getItem();
    }
}
