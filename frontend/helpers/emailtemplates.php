<?php
/**
 *  @package UserSubs
 *  @copyright Copyright (c)2015 Nicholas K. Dionysopoulos, Juloa
 *  @license GNU General Public License version 3, or later
 *  Code adapted from Akeeba Subscription
 */

defined('_JEXEC') or die();
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_adsmanager'.DS.'models'.DS.'field.php');
require_once(JPATH_ROOT.DS.'components'.DS.'com_adsmanager'.DS.'helpers'.DS.'field.php');


class EmailTemplateHelperEmail
{

	/**
	 * Loads an email template from the database or, if it doesn't exist, from
	 * the language file.
	 *
	 * @param   string   $key    The language key, in the form PLG_LOCATION_PLUGINNAME_TYPE
	 *
	 * @return  array  isHTML: If it's HTML override from the db; text: The unprocessed translation string
	 */
	private static function loadEmailTemplate($key, $user = null)
	{
		if (is_null($user)) {
			$user = JFactory::getUser();
		}

		// Initialise
		$templateText = '';
		$subject = '';
		$isHTML = false;

		// Look for desired languages
		$jLang = JFactory::getLanguage();
		$userLang = $user->getParam('language', '');
		$languages = array(
			$userLang, $jLang->getTag(), $jLang->getDefault(), 'en-GB', '*'
		);

		// Look for an override in the database
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__adsmanager_emailtemplates'))
		->where($db->qn('key') . '=' . $db->q($key))
		->where($db->qn('published') . '=' . $db->q(1));
		$db->setQuery($query);
		$allTemplates = $db->loadObjectList();

		if (!empty($allTemplates)) {
			// Pass 1 - Give match scores to each template
			$preferredIndex = null;
			$preferredScore = 0;
			foreach ($allTemplates as $idx => $template) {
				// Get the language and level of this template
				$myLang = $template->language;

				// Make sure the language matches one of our desired languages, otherwise skip it
				$langPos = array_search($myLang, $languages);
				if ($langPos === false) {
					continue;
				}
				$langScore = (5 - $langPos);

				// Calculate the score. If it's winning, use it
				$score = $langScore;
				if ($score > $preferredScore) {
					$loadLanguage = $myLang;
					$subject = $template->subject;
					$templateText = $template->body;
					$preferredScore = $score;

					$isHTML = true;
				}
			}
		}

		return array($isHTML, $subject, $templateText);
	}

	/**
	 * Creates a PHPMailer instance
	 *
	 * @param   boolean  $isHTML
	 *
	 * @return  PHPMailer  A mailer instance
	 */
	private static function &getMailer($isHTML = true)
	{
		$mailer = clone JFactory::getMailer();

		$mailer->IsHTML($isHTML);
		// Required in order not to get broken characters
		$mailer->CharSet = 'UTF-8';

		return $mailer;
	}

	/**
	 * Creates a mailer instance, preloads its subject and body with your email
	 * data based on the key and extra substitution parameters and waits for
	 * you to send a recipient and send the email.
	 *
	 * @param   object  $item     The subscription record against which the email is sent
	 * @param   string  $key     The email key, in the form PLG_LOCATION_PLUGINNAME_TYPE
	 * @param   array   $extras  Any optional substitution strings you want to introduce
	 *
	 * @return  boolean|PHPMailer False if something bad happened, the PHPMailer instance in any other case
	 */
	public static function getPreloadedMailer($item, $key, array $extras = array())
	{
		// Load the template
		list($isHTML, $subject, $templateText) = self::loadEmailTemplate($key, JFactory::getUser($item->userid));
		if (empty($subject)) {
			return false;
		}

		$templateText = self::replaceTags($templateText, $item, $extras);
		$subject = self::replaceTags($subject, $item, $extras);

		// Get the mailer
		$mailer = self::getMailer($isHTML);
		$mailer->setSubject($subject);

		// Include inline images
		$pattern = '/(src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $templateText, $matches, PREG_OFFSET_CAPTURE);
		if ($number_of_matches > 0) {
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			$imgidx = 0;
			$imageSubs = array();
			foreach ($substitutions as &$entry) {
				// Copy unchanged part, if it exists
				if ($entry[1] > 0)
				$temp .= substr($templateText, $last_position, $entry[1] - $last_position);
				// Examine the current URL
				$url = $entry[0];
				if ((substr($url, 0, 7) == 'http://') || (substr($url, 0, 8) == 'https://')) {
					// External link, skip
					$temp .= $url;
				} else {
					$ext = strtolower(JFile::getExt($url));
					if (!JFile::exists($url)) {
						// Relative path, make absolute
						$url = dirname($template) . '/' . ltrim($url, '/');
					}
					if (!JFile::exists($url) || !in_array($ext, array('jpg', 'png', 'gif'))) {
						// Not an image or inexistent file
						$temp .= $url;
					} else {
						// Image found, substitute
						if (!array_key_exists($url, $imageSubs)) {
							// First time I see this image, add as embedded image and push to
							// $imageSubs array.
							$imgidx++;
							$mailer->AddEmbeddedImage($url, 'img' . $imgidx, basename($url));
							$imageSubs[$url] = $imgidx;
						}
						// Do the substitution of the image
						$temp .= 'cid:img' . $imageSubs[$url];
					}
				}

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}
			// Do we have any remaining part of the string we have to copy?
			if ($last_position < strlen($templateText))
			$temp .= substr($templateText, $last_position);
			// Replace content with the processed one
			$templateText = $temp;
		}

		$mailer->setBody($templateText);

		return $mailer;
	}

    /**
	 * Sends out the email to the owner of the subscription.
	 *
	 * @param $item USersubsSubscription The subscription row object
	 * @param $type string The type of the email to send (generic, new,)
	 */
	public static function sendEmail($item, $key, $conf = null, $extras = array())
	{
		// Get the user object
		$user = JFactory::getUser($item->userid);

		// Get a preloaded mailer
		$mailer = self::getPreloadedMailer($item, $key, $extras);

		if ($mailer === false) {
			return false;
		}

		// Select admin, alert or user email according to $key
		if (substr($key, 0, 5) == 'admin') {

			$config	= JFactory::getConfig();

			if(isset($conf->email_admin_mode) && $conf->email_admin_mode == 1) {
				//send to an usergroup
				$groups = $conf->email_admin_usergroups;
				$sqlGroups = implode(',', $groups);

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $select = array( 'users.id', 'users.email');
        $where = $db->quoteName('map.group_id') . ' IN (' . $sqlGroups . ')';

        $query
            ->select($select)
            ->from( $db->quoteName('#__user_usergroup_map', 'map') )
            ->leftJoin( $db->quoteName('#__users', 'users') . ' ON (map.user_id = users.id)' )
            ->where($where);

        $db->setQuery($query);
        $adminUsers = $db->loadObjectList();
				foreach($adminUsers as $adminUser) {
					$mailer->addRecipient($adminUser->email);
				}
			} else {
				//send to a specific email
				if ($conf->email_admin != "") {
					$adminEmail = $conf->email_admin;
				} else {
					$adminEmail = $config->get('mailfrom');
				}
				$mailer->addRecipient($adminEmail);
			}

		} else {
			$mailer->addRecipient($user->email);
		}

		$result = $mailer->Send();
		$mailer = null;

		return $result;
	}
    
        /**
	 * Pre-processes the message text in $text, replacing merge tags with those
	 * fetched based on subscription $sub
	 *
	 * @param   string  $text    The message to process
	 * @param   Subscription  $sub  A subscription object
	 *
	 * @return  string  The processed string
	 */
	public static function replaceTags($text, $item, $extras = array())
	{
		if(is_array($item)) {
			$item = (object)$item;
		}
		
		$uri	= JURI::getInstance();
		$baseUrl	= JURI::base();

		// Get the extra user parameters object for the subscription

		$user = JFactory::getUser($item->userid);

		// -- Get the site name
		$config = JFactory::getConfig();

        $sitename = $config->get('sitename');

		

		$fieldmodel = new AdsmanagerModelField();
		$fields = $fieldmodel->getFields();
		$field_values = $fieldmodel->getFieldValues();
		$plugins = $fieldmodel->getPlugins();
		$conf = TConf::getConfig();
		$field = new JHTMLAdsmanagerField($conf, $field_values, 1, $plugins, '', $baseUrl, null);

		if(isset($extras['alert_list']) && $extras['alert_list'] == true) {
			$ads = $extras['ads'];
			$alertContent = '';
			$extras = array();
			$alertContent .= '<ul style="list-style:none;margin:auto;max-width:600px;">';
			foreach($ads as $ad) {
				$link = JRoute::_($baseUrl.'index.php?option=com_adsmanager&view=details&id='.$ad->id.'&catid='.$ad->catid);
				$alertContent .= '<li><a href="'.$link.'" style="text-decoration: none;color: #000" target="_blank">';
				$alertContent .= '<table style="width: 100%;">';
				$alertContent .= '<tr><td width="30%">';
				$alertContent .= '<img src="'.$baseUrl.'images/com_adsmanager/contents/'.$ad->images[0]->thumbnail.'" alt="'.$ad->ad_headline.'" />';
				$alertContent .= '</td>';
				$alertContent .= '<td width="70%">';
				$alertContent .= '<h2>'.$ad->ad_headline.'</h2>';
				$af_text = JString::substr($ad->ad_text, 0, 100);
				if (strlen($ad->ad_text)>100) {
					$af_text .= "[...]";
				}
				$alertContent .= '<span>'.$af_text.'</span>';
				$alertContent .= '</td>';
				$alertContent .= '</tr></table>';
				$alertContent .= '</a></li>';
			}
			$alertContent .= '</ul>';
			
			$extras["{list_ads}"] = $alertContent;
			$extras["{cpt_ads}"] = count($ads);
			$link = '';
			$expirationLink = '';
			if(!isset($item->expiration_date)) {
				$item->expiration_date = '';
			}
		} else if (isset($extras['alert_new_ad']) && $extras['alert_new_ad'] == true) {
			$ad = $extras['ads'];
			$alertContent = '';
			$extras = array();
			$alertContent .= '<ul style="list-style:none;margin:auto;max-width:600px;">';
			$link = JRoute::_($baseUrl.'index.php?option=com_adsmanager&view=details&id='.$ad->id.'&catid='.$ad->catid);
			$alertContent .= '<li><a href="'.$link.'" style="text-decoration: none;color: #000" target="_blank">';
			$alertContent .= '<table style="width: 100%;">';
			$alertContent .= '<tr><td width="30%">';
			$alertContent .= '<img src="'.$baseUrl.'images/com_adsmanager/contents/'.$ad->images[0]->thumbnail.'" alt="'.$ad->ad_headline.'" />';
			$alertContent .= '</td>';
			$alertContent .= '<td width="70%">';
			$alertContent .= '<h2>'.$ad->ad_headline.'</h2>';
			$af_text = JString::substr($ad->ad_text, 0, 100);
			if (strlen($ad->ad_text)>100) {
				$af_text .= "[...]";
			}
			$alertContent .= '<span>'.$af_text.'</span>';
			$alertContent .= '</td>';
			$alertContent .= '</tr></table>';
			$alertContent .= '</a></li>';
			$alertContent .= '</ul>';
			
			$extras["{new_ad}"] = $alertContent;
			$link = '';
			$expirationLink = '';
			if(!isset($item->expiration_date)) {
				$item->expiration_date = '';
			}
		} else {
			$link= JRoute::_($baseUrl.'index.php?option=com_adsmanager&view=details&id='.$item->id.'&catid='.$item->catid);
			$expirationLink=JRoute::_($baseUrl.'index.php?option=com_adsmanager&view=expiration&id='.$item->id);
			
			foreach ($fields as $f) {
				$fvalue = "";
				if (strpos($text, $f->name) !== false) {
					$fvalue = str_replace(["<br/>", "<br />", "<br>"], "", $field->showFieldValue($item, $f));
					$text = nl2br(str_replace("{" . $f->name . "}", $fvalue, $text));
				}
			}
		}
		
		// -- The actual replacement
		$extras = array_merge([
			"\\n"                       => "\n",
			'{id}'						=> $item->id,
			'{sitename}'                => $sitename,
			'{link}' 	            	=> "<a href='$link' target='_blank'>$link</a>",
			'{expiration_link}'			=> "<a href='$expirationLink' target='_blank'>$expirationLink</a>",
			'{expiration_date}'         => $item->expiration_date,
			'{siteurl}'                 => $baseUrl,
			'{fullname}'                => $user->name,
			'{username}'                => $user->username,
			'{useremail}'               => $user->email,
		], $extras);
		foreach ($extras as $key => $value)
		{
            $text = str_replace($key, $value, $text);
		}
		
		return $text;
	}
}