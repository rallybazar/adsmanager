<?php
/**
 * @package     AdsManager
 * @copyright   Copyright (C) 2010-2025 Juloa.com. All rights reserved.
 * @license     GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class AdsmanagerModelPremiumads extends TModel
{
    /**
     * Vracia počet premium ads podľa filtrov
     * @param array $filters
     * @param int $adminFlag
     * @return int
     */
    public function getNbPremiumAds($filters = array(), $adminFlag = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__adsmanager_premium_ads'));

        
        if (!empty($filters['id'])) {
            $query->where($db->quoteName('id') . ' = ' . (int)$filters['id']);
        }

// Filter podľa publikácie
        if (!empty($filters['publish'])) {
            $query->where($db->quoteName('published') . ' = ' . (int)$filters['publish']);
        }

        // Filter podľa user ID
        if (!empty($filters['userid'])) {
            $query->where($db->quoteName('userid') . ' = ' . (int)$filters['userid']);
        }

        // Filter podľa headline (textový filter)
        if (!empty($filters['headline'])) {
            $query->where($db->quoteName('headline') . ' LIKE ' . $db->quote('%' . $filters['headline'] . '%'));
        }

        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    /**
     * Vracia samotné premium ads podľa filtrov a stránkovania
     * @param array $filters
     * @param int $limitstart
     * @param int $limit
     * @param string $order
     * @param string $orderDir
     * @param int $adminFlag
     * @return array
     */
    public function getPremiumAds($filters = array(), $limitstart = 0, $limit = 20, $order = 'id', $orderDir = 'DESC', $adminFlag = 1)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
              ->from($db->quoteName('#__adsmanager_premium_ads'));
        
        if (!empty($filters['id'])) {
            $query->where($db->quoteName('id') . ' = ' . (int)$filters['id']);
        }
        
      // Filtre
        if (!empty($filters['publish'])) {
            $query->where($db->quoteName('published') . ' = ' . (int)$filters['publish']);
        }

        if (!empty($filters['userid'])) {
            $query->where($db->quoteName('userid') . ' = ' . (int)$filters['userid']);
        }

        if (!empty($filters['headline'])) {
            $query->where($db->quoteName('headline') . ' LIKE ' . $db->quote('%' . $filters['headline'] . '%'));
        }

        // ORDER BY - použitie skutočných názvov stĺpcov tabuľky
        $query->order($db->escape($order) . ' ' . $db->escape($orderDir));

        // Nastavenie query a limit
        $db->setQuery($query, (int)$limitstart, (int)$limit);

        return $db->loadObjectList();
    }

    public function saveData($data)
    {
        $db = JFactory::getDbo();
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if ($id) {
            // UPDATE
            $fields = array(
                $db->quoteName('userid') . ' = ' . (int)$data['userid'],
                $db->quoteName('headline') . ' = ' . $db->quote($data['headline']),
                $db->quoteName('description') . ' = ' . $db->quote($data['description']),
                $db->quoteName('price') . ' = ' . $db->quote($data['price']),
                $db->quoteName('image') . ' = ' . $db->quote($data['image']),
                $db->quoteName('url') . ' = ' . $db->quote($data['url']),
                $db->quoteName('custom_html') . ' = ' . $db->quote($data['custom_html']),
                $db->quoteName('priority') . ' = ' . (int)$data['priority'],
                $db->quoteName('active_from') . ' = ' . $db->quote($data['active_from']),
                $db->quoteName('active_to') . ' = ' . $db->quote($data['active_to']),
                $db->quoteName('published') . ' = ' . (int)$data['published']
            );

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__adsmanager_premium_ads'))
                ->set($fields)
                ->where($db->quoteName('id') . ' = ' . $id);
        } else {
            // INSERT
            $columns = array(
                'userid',
                'headline',
                'description',
                'price',
                'image',
                'url',
                'custom_html',
                'priority',
                'active_from',
                'active_to',
                'published'
            );

            $values = array(
                (int)$data['userid'],
                $db->quote($data['headline']),
                $db->quote($data['description']),
                $db->quote($data['price']),
                $db->quote($data['image']),
                $db->quote($data['url']),
                $db->quote($data['custom_html']),
                (int)$data['priority'],
                $db->quote($data['active_from']),
                $db->quote($data['active_to']),
                (int)$data['published']
            );

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__adsmanager_premium_ads'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));
        }

        $db->setQuery($query);

        try {
            return $db->execute();
        } catch (Exception $e) {
            JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Vracia jeden premium ad podľa ID
     * @param int $id
     * @return object|null
     */
    public function getItem($id = 0)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__adsmanager_premium_ads'));

        if ($id) {
            $query->where($db->quoteName('id') . ' = ' . (int)$id);
        } else {
            return null;
        }

        $db->setQuery($query);
        return $db->loadObject();
    }

    public function deleteItem($id)
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__adsmanager_premium_ads'))
                    ->where($db->quoteName('id') . ' = ' . (int)$id);

        $db->setQuery($query);
        try {
            return $db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

}
