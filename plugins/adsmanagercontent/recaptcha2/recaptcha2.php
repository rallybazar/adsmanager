<?php
/**
 * Adaptation of Recaptcha Plugin for Adsmanager working on 1.5/2.5/3.0
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.environment.browser');

/**
 * Recaptcha Plugin.
 * Based on the official recaptcha library( https://developers.google.com/recaptcha/docs/php )
 *
 * @package     Joomla.Plugin
 * @subpackage  Captcha
 * @since       2.5
 */
class plgAdsmanagercontentRecaptcha2 extends JPlugin
{
	const RECAPTCHA_API_SERVER = "http://www.google.com/recaptcha/api";
	const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";
	const RECAPTCHA_VERIFY_SERVER = "www.google.com";

	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	public function ADSonContentBeforeSave() {
		return $this->onCheckAnswer(null);
	}
	public function ADSonUserBeforeSave() {
		return $this->onCheckAnswer(null);
	}
	public function ADSonMessageBeforeSend() {
		return $this->onCheckAnswer(null);
	}

	public function ADSonContentAfterForm($content) {
		return $this->displayCaptcha();
	}

	public function ADSonUserAfterForm($user) {
		return $this->displayCaptcha();
	}
	public function ADSonMessageAfterForm($content) {
		return $this->displayCaptcha();
	}

	public function displayCaptcha() {
		$displayEdit = $this->params->get('edit_form', '0');
		$displayMessage = $this->params->get('message_form', '0');
		$displayProfile = $this->params->get('profile_form', '0');

		$view = JFactory::getApplication()->input->get('view','');
		$task = JFactory::getApplication()->input->get('task','');

		if ((($view == 'edit' || $task =='write') && $displayEdit == 1) ||
			($view == 'profile' && $displayProfile == 1) ||
			($view == 'message' && $displayMessage == 1)) {
			$this->onInit(null);
			$html  = "<tr><td>".JText::_('ADSMANAGER_SECURITY_CODE')."</td><td>";
			$html .= $this->onDisplay(null,null,null);
			$html .= "</td></tr>";
			return $html;
		}
		return '';
	}

	/**
	 * Initialise the captcha
	 *
	 * @param	string	$id	The id of the field.
	 *
	 * @return	Boolean	True on success, false otherwise
	 *
	 * @since  2.5
	 */
	public function onInit($id)
	{
		// Initialise variables
		//$lang		= $this->_getLanguage();
		$pubkey		= $this->params->get('public_key', '');


		if ($pubkey == null || $pubkey == '')
		{
			throw new Exception(JText::_('PLG_RECAPTCHAX_ERROR_NO_PUBLIC_KEY'));
		}


		JHtml::_('script', 'https://www.google.com/recaptcha/api.js');

		return true;
	}

	/**
	 * Gets the challenge HTML
	 *
	 * @param   string  $name   The name of the field.
	 * @param   string  $id     The id of the field.
	 * @param   string  $class  The class of the field.
	 *
	 * @return  string  The HTML to be embedded in the form.
	 *
	 * @since  2.5
	 */
	public function onDisplay($name, $id, $class)
	{
		//return '<div id="dynamic_recaptcha_1"></div>';
		$pubkey		= $this->params->get('public_key', '');
		$theme		= $this->params->get('theme', 'clean');
		return '<div class="g-recaptcha" data-sitekey="'.$pubkey.'" data-theme="'.$theme.'"></div>';
	}



	/**
	 * Calls an HTTP POST function to verify if the user's guess was correct
	 *
	 * @return  True if the answer is correct, false otherwise
	 *
	 * @since  2.5
	 */
	public function onCheckAnswer($code)
	{

		$displayEdit = $this->params->get('edit_form', '0');
		$displayMessage = $this->params->get('message_form', '0');
		$displayProfile = $this->params->get('profile_form', '0');

		$view = JFactory::getApplication()->input->get('view','');
		$task = JFactory::getApplication()->input->get('task','');

		if(($task == 'save' && $displayEdit == 1) ||
			($view == 'profile' && $displayProfile == 1) ||
			($view == 'message' && $displayMessage == 1) ||
			($task == 'sendmessage' && $displayMessage == 1) ||
			($task == 'saveprofile' && $displayProfile == 1)) {
			// Initialise variables
			$privatekey	= $this->params->get('private_key');
			$remoteip	= JRequest::getVar('REMOTE_ADDR', '', 'SERVER');
			$captchax	= JRequest::getString('g-recaptcha-response', '');



			// Check for Private Key
			if (empty($privatekey))
			{
				throw new Exception(JText::_('PLG_RECAPTCHAX_ERROR_NO_PRIVATE_KEY'));
				return false;
			}

			// Check for IP
			if (empty($remoteip))
			{
				throw new Exception(JText::_('PLG_RECAPTCHAX_ERROR_NO_IP'));
				return false;
			}

			// Discard spam submissions
			if ($captchax == null || strlen($captchax) == 0)
			{
				throw new Exception(JText::_('PLG_RECAPTCHAX_ERROR_EMPTY_SOLUTION'));
				return false;
			}


			$url = 'https://www.google.com/recaptcha/api/siteverify';
				$data = array(
					'secret' => $privatekey,
					'response' => $_POST["g-recaptcha-response"]
				);
				$options = array(
					'http' => array (
						'header' => 'Content-Type: application/x-www-form-urlencoded',
						'method' => 'POST',
						'content' => http_build_query($data)
					)
				);
				$context  = stream_context_create($options);
				$verify = file_get_contents($url, false, $context);
				$captcha_success=json_decode($verify);


			if ($captcha_success->success==true) {
				return true;

			} else if ($captcha_success->success==false) {

			throw new Exception(JText::_('PLG_RECAPTCHAX_ERROR_'.strtoupper(str_replace('-', '_', $captcha_success->{'error-codes'}[0]))));
		return false;
			}


		}
		return '';
	}


	/**
	 * Get the language tag or a custom translation
	 *
	 * @return string
	 *
	 * @since  2.5
	 */
	private function _getLanguage()
	{
		// Initialise variables
		$language = JFactory::getLanguage();

		$tag = explode('-', $language->getTag());
		$tag = $tag[0];
		$available = array('en', 'pt', 'fr', 'de', 'nl', 'ru', 'es', 'tr');

		if (in_array($tag, $available))
		{
			return "lang : '" . $tag . "',";
		}

		// If the default language is not available, let's search for a custom translation
		if ($language->hasKey('PLG_RECAPTCHA_CUSTOM_LANG'))
		{
			$custom[] ='custom_translations : {';
			$custom[] ="\t".'instructions_visual : "' . JText::_('PLG_RECAPTCHA_INSTRUCTIONS_VISUAL') . '",';
			$custom[] ="\t".'instructions_audio : "' . JText::_('PLG_RECAPTCHA_INSTRUCTIONS_AUDIO') . '",';
			$custom[] ="\t".'play_again : "' . JText::_('PLG_RECAPTCHA_PLAY_AGAIN') . '",';
			$custom[] ="\t".'cant_hear_this : "' . JText::_('PLG_RECAPTCHA_CANT_HEAR_THIS') . '",';
			$custom[] ="\t".'visual_challenge : "' . JText::_('PLG_RECAPTCHA_VISUAL_CHALLENGE') . '",';
			$custom[] ="\t".'audio_challenge : "' . JText::_('PLG_RECAPTCHA_AUDIO_CHALLENGE') . '",';
			$custom[] ="\t".'refresh_btn : "' . JText::_('PLG_RECAPTCHA_REFRESH_BTN') . '",';
			$custom[] ="\t".'help_btn : "' . JText::_('PLG_RECAPTCHA_HELP_BTN') . '",';
			$custom[] ="\t".'incorrect_try_again : "' . JText::_('PLG_RECAPTCHA_INCORRECT_TRY_AGAIN') . '",';
			$custom[] ='},';
			$custom[] ="lang : '" . $tag . "',";

			return implode("\n", $custom);
		}

		// If nothing helps fall back to english
		return '';
	}



}
