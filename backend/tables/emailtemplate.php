<?php
/**
 * @author 		Anthony Verdure 
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2021 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die();

class AdsmanagerTableEmailtemplate extends JTable
{
	public $id = null;
	public $key ;
	public $subject;
	public $body = null;
	public $language = '*';
	public $published = 1;
	public $ordering = 0;
	public $created_on = null;
	public $created_by = 0;
	public $modified_on = null;
	public $modified_by = 0;
	public $locked_on = null;
	public $locked_by = 0;

    function __construct(&$db)
    {
    	parent::__construct( '#__adsmanager_emailtemplates', 'id', $db );
    }
}