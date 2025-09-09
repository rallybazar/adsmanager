<?php
/**
 * @package             Joomla.Cli
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER)) die();

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/update_cron.php
 */

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(dirname(__FILE__)) . '/defines.php'))
{
        require_once dirname(dirname(__FILE__)) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
        define('JPATH_BASE', dirname(dirname(__FILE__)));
        require_once JPATH_BASE . '/includes/defines.php';
}

// Load the rest of the framework include files
if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
        require_once JPATH_LIBRARIES . '/import.legacy.php';
}
else
{
        require_once JPATH_LIBRARIES . '/import.php';
}
require_once JPATH_LIBRARIES . '/cms.php';

// Force library to be in JError legacy mode
JError::$legacy = true;

require_once JPATH_CONFIGURATION . '/configuration.php';

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @package  Joomla.CLI
 * @since    2.5
*/
class AdsManagerCron extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function doExecute()
	{
		// Find all updates
		// Fool the system into thinking we are running as JSite with Smart Search as the active component.
		$config = JFactory::getConfig();
		$live_site = $config->get('live_site');
		if ($live_site == "") {
			echo "you must set live_site=http://www.mydomain.com in your configuration.ini";
		}
		
		$_SERVER['HTTP_HOST'] = str_replace(array('http://','https://'),'',$live_site);
		JFactory::getApplication('site');// Fool the system into thinking we are running as JSite
		require_once(JPATH_ROOT.'/components/com_adsmanager/lib/core.php');
		require_once(JPATH_ROOT.'/components/com_adsmanager/controller.php');
		
		$this->out('AdsManager cron start...');
		TCron::execute();
		$this->out('AdsManager cron finish');
	}
}

JApplicationCli::getInstance('AdsManagerCron')->execute();