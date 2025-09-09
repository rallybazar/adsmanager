<?php
defined('_JEXEC') or die;

/**
 * Options for Result Menu Item.
 */
class PlgContentAdsmenu extends JPlugin
{
	public function onContentPrepareForm( $form, $data)
	{
		$formName = $form->getName();
		$modifyForm = false;
		// check if this is a menu item for my component
		if(is_array($data) && $formName == 'com_menus.item' && array_key_exists('component_id', $data))
		{
			$myComponent = JComponentHelper::getComponent('com_adsmanager');
			if($data->component_id == $myComponent->id && $data->request['view'] == "result")
			{	
				$modifyForm = true;
			}
		}

		if($modifyForm)
		{

			  $db =JFactory::getDBO();
       		          $query = "SELECT name,title FROM #__adsmanager_fields WHERE published =1 ORDER BY title ASC";
	              	  $db->setQuery($query);
		          $fields = $db->loadObjectList();

			$textXml = '<fields name="request"><fieldset name="adsfields" label="AdsManager Fields" >"';
			$textXml .= '<field type="hidden" name="new_search" default="1" />';
			foreach($fields as $f) {
				$textXml .= '<field type="text" name="'.$f->name.'" label="'.htmlspecialchars($f->title).'" />';
			}
			$textXml .= '</fieldset></fields>';
			$xmlElement = new SimpleXMLElement($textXml);
			$form->setField($xmlElement);
		} 
	} 
}
