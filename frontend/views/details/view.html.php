<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');

require_once(JPATH_BASE."/components/com_adsmanager/helpers/field.php");
require_once(JPATH_BASE."/components/com_adsmanager/helpers/general.php");

/**
 * @package		Joomla
 * @subpackage	Contacts
 */
class AdsmanagerViewDetails extends TView
{
	
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		$user		= JFactory::getUser();
		$pathway	= $app->getPathway();
		$document	= JFactory::getDocument();
		
		$contentmodel	=$this->getModel( "content" );
		$catmodel		=$this->getModel( "category" );
		$positionmodel	=$this->getModel( "position" );
		$fieldmodel	    =$this->getModel( "field" );

		// Get the parameters of the active menu item
		$menus	= $app->getMenu();
		$menu    = $menus->getActive();
		
		$conf = TConf::getConfig();
		
		$catid = JRequest::getInt( 'catid',	0 );
		if ($catid != "0") {
			$category = $catmodel->getCategory($catid);
			$category->img = TTools::getCatImageUrl($catid,true);
		}
		else
		{
			$category = new stdClass();
			$category->name = JText::_("ADSMANAGER_ALL_ADS");
			$category->description = "";
			$category->img = "";
		}
		
		$rootid = JRequest::getInt('rootid',0);
		
		$pathlist = $catmodel->getPathList($catid,'read',$rootid);
		$this->assignRef('pathlist',$pathlist);
		
		$positions = $positionmodel->getPositions('details');
		$fDisplay = $fieldmodel->getFieldsbyPositions();
		
		$field_values = $fieldmodel->getFieldValues();
		
		$contentid = JRequest::getInt( 'id',	0 );
		$content = $contentmodel->getContent($contentid,false);
		if (($content->published == false)&&($content->userid != $user->id)) {
			if (ADSMANAGER_SPECIAL == 'sale') {
				require_once(JPATH_ROOT.'/administrator/components/com_sale/models/configuration.php');
				$model = new SaleModelConfiguration();
				$saleconf = $model->getConfiguration();
				if ($content->userid != $saleconf->userid) {
					$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list&catid='.$catid));
				}
				//Display Ad
			} else if (ADSMANAGER_SPECIAL == 'barter') {
			} else {
				//JError::raiseError(404, JText::_("Page Not Found"));
				$app->redirect( TRoute::_('index.php?option=com_adsmanager&view=list&catid='.$catid));
			}
		}
		
		if ($content == null) {
			$app->redirect($_SERVER['HTTP_REFERER'], JText::_('ADSMANAGER_ADS_NO_RIGHT'),'message' );
		}
		
        if($user->guest == false){
            $favorites = $contentmodel->getFavorites($user->id);
        } else {
            $favorites = array();
        }
        $this->assignRef('favorites',$favorites);
        
        $showContact = TPermissions::checkRightContact();
		
        $this->assignRef('showContact',$showContact);
		$this->assignRef('list_name',$category->name);
		$this->assignRef('list_img',$category->img);
		$this->assignRef('list_description',$category->description);
		$this->assignRef('positions',$positions);	
		$this->assignRef('fDisplay',$fDisplay);	
		$this->assignRef('conf',$conf);
		$this->assignRef('userid',$user->id);
		
		$fields = $fieldmodel->getFields();
		$this->assignRef('fields',$fields);
		
		$document->setTitle( JText::_('ADSMANAGER_PAGE_TITLE')." ".$content->ad_headline);

		//set breadcrumbs 
		$pathlist = $catmodel->getPathList($catid,'read',$rootid);
		$nb = count($pathlist);
		for ($i = $nb - 1 ; $i >=0;$i--)
		{
			$pathway->addItem($pathlist[$i]->text, $pathlist[$i]->link);
		}
		
		// need to be before getMultiLangText
		$plugins = $fieldmodel->getPlugins();
		
		if (ADSMANAGER_SPECIAL == "abrivac") {
			$pathway->addItem(getMultiLangText($content->ad_headline), "#");
		} else {
			$pathway->addItem($content->ad_headline, "#");
		}
		
		
		$field = new JHTMLAdsmanagerField($conf,$field_values,'1',$plugins);
		
		$this->assignRef('field',$field);
		
		$general = new JHTMLAdsmanagerGeneral($catid,$conf,$user);
		$this->assignRef('general',$general);
		
		//
		// Process the content plugins.
		//
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('adsmanagercontent');
		
		
		$results = $dispatcher->trigger('ADSonContentPrepare', array ($content));

		$event = new stdClass();
		$results = $dispatcher->trigger('ADSonContentAfterTitle', array ($content));
		$event->onContentAfterTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('ADSonContentBeforeDisplay', array ($content));
		$event->onContentBeforeDisplay = trim(implode("\n", $results));

		$results = $dispatcher->trigger('ADSonContentAfterDisplay', array ($content));
		$event->onContentAfterDisplay = trim(implode("\n", $results));
		
		$content->event = $event;
		$this->assignRef('content',$content);
		
		if ($conf->metadata_mode == 'automatic') {
			if (ADSMANAGER_SPECIAL == "abrivac") {
				$content->metadata_description = getMultiLangText($content->ad_description);
				$content->metadata_keywords = str_replace(" ",",",getMultiLangText($content->ad_headline));
			} else {
				$content->metadata_description = $content->ad_text;
				$content->metadata_keywords = str_replace(" ",",",$content->ad_headline);
			}
		}

		// Last Ads: načítame posledné inzeráty z aktuálnej kategórie
		$filters['category'] = $catid;
		$filters['exclude_id'] = $contentid;  // vylúči aktuálny inzerát
		$limitstart = 0;
		$limit = 6;

		// $lastContents = $contentmodel->getContents($filters, $limitstart, $limit, 'date_created', 'DESC');
		// Náhodné poradie, aby sa zobrazenie menilo pri každom načítaní
		$lastContents = $contentmodel->getContents($filters, $limitstart, $limit, 'RAND()', '');
		$this->assignRef('contents', $lastContents);


        if($conf->image_display == 'jssor') {
            $tpl = 'jssor';
        }
        
		parent::display($tpl);
	}
	
	function isNewContent($date,$nbdays) {
		$time = strtotime($date);
		if ($time >= (time()-($nbdays*24*3600)))
			return true;
		else
			return false;
	}

	function displayContents($contents,$nbimages) {
		// toto je tá funkcia
		$conf = TConf::getConfig();
	?>
		<h1 class="contentheading"><?php echo JText::_('ADSMANAGER_SIMILIAR_ADS');?></h1>
		<!-- upraviť funkciu tak aby zobrazovala podobné inzeráty z danej kategórie -->
		<div class='adsmanager_box_module' align="center">
			<table class='adsmanager_inner_box' width="100%">
				<!-- chýba tu nejaká premenná aby som vedel načítať list -->
			<?php
			$nb_cols = $conf->nb_last_cols;
			$col = 0;
			foreach($contents as $row) {
				if ($col == 0) 
					echo '<tr align="center">';
				$col++;
			?>
				<td>
				<?php	
				$linkTarget = TRoute::_("index.php?option=com_adsmanager&view=details&id=".$row->id."&catid=".$row->catid);			
				if (isset($row->images[0])) {
					echo "<div align='center'><a href='".$linkTarget."'><img src='".JURI_IMAGES_FOLDER."/".$row->images[0]->thumbnail."' alt=\"".htmlspecialchars($row->ad_headline)."\" border='0' /></a>";
				} else if ($conf->nb_images > 0) {
					echo "<div align='center'><a href='".$linkTarget."'><img src='".ADSMANAGER_NOPIC_IMG."' alt='nopic' border='0' /></a>"; 
				} 	
					
				echo "<br /><b><a href='$linkTarget'>".$row->ad_headline."</a></b>"; 
				// echo "<br /><span class=\"adsmanager_cat\">(".htmlspecialchars($row->parent)." / ".htmlspecialchars($row->cat).")</span>";
				echo "<br /><span class=\"adsmanager_cat\">(".htmlspecialchars($row->cat).")</span>";
				echo "<br />".$this->reorderDate($row->date_created);
				echo "</div>";
				?>
				</td>
			<?php
				if ($col == $nb_cols) {
					echo "</tr>";
					$col = 0;	
				}
			}
			if ($col != 0) {
				echo "</tr>";
			}
			?>
			</table>
		</div>
	<br />
	<?php
	}

	function displayLastAdsList($contents, $nb = 5) {
		if (empty($contents) || !is_array($contents)) return;

		$lastContents = array_slice($contents, 0, $nb);
		?>
		<div class="container-fluid">
			<h4 class="mb-3 text-dark"><?php echo JText::_('ADSMANAGER_SIMILIAR_ADS'); ?></h4>

			<?php if (!empty($lastContents)): ?>
			<table class="table w-100">
				<tbody>
				<?php foreach($lastContents as $content):
					$linkTarget = TRoute::_("index.php?option=com_adsmanager&view=details&id=".$content->id."&catid=".$content->catid);
				?>
					<tr class="adsmanager_table_description trcategory_<?php echo $content->catid; ?>" 
						style="transition: background-color 0.3s;"
						onclick="window.location='<?php echo $linkTarget; ?>'">

						<!-- Ľavý stĺpec: fotka -->
						<td style="width: 35%; vertical-align: top; padding: 15px; text-align:center;">
							<?php if (isset($content->images[0])): ?>
								<a href="<?php echo $linkTarget; ?>">
									<img class="fad-image img-fluid rounded" 
										src="<?php echo JURI_IMAGES_FOLDER."/".$content->images[0]->thumbnail; ?>" 
										alt="<?php echo htmlspecialchars($content->ad_headline); ?>" />
								</a>
							<?php else: ?>
								<a href="<?php echo $linkTarget; ?>">
									<img class="fad-image img-fluid rounded" 
										src="<?php echo ADSMANAGER_NOPIC_IMG; ?>" 
										alt="nopic" />
								</a>
							<?php endif; ?>
						</td>

						<!-- Pravý stĺpec: nadpis + info -->
						<td style="width: 65%; vertical-align: top; padding: 15px;">
							<div style="display:flex; flex-direction:column; justify-content:flex-start; height:100%;">
								<!-- Nadpis -->
								<h4 class="fw-bold mb-1 text-dark juloawrapper" style="margin-top:0;">
									<a href="<?php echo $linkTarget; ?>" class="text-dark">
										<b><?php echo htmlspecialchars($content->ad_headline); ?></b>
									</a>
								</h4>

								<div class="mb-1 text-dark">
									<?php if (!empty($content->ad_price) || !empty($content->ad_priceother)): ?>
										<?php 
										$priceLine = '';
										if (!empty($content->ad_price)) $priceLine .= htmlspecialchars($content->ad_price)." €";
										echo $priceLine . "<br/>";
										endif; 
										?>
									<?php if (!empty($content->ad_city)) echo htmlspecialchars($content->ad_city) . "<br/>"; ?>
									<?php if($content->userid != 0): 
										$target = TLink::getUserAdsLink($content->userid);
										echo "od " . ($this->conf->display_fullname == 1 
											? "<a href='".$target."' class='text-dark'><b>".$content->name."</b></a>" 
											: "<a href='".$target."' class='text-dark'><b>".$content->user."</b></a>"); 
									endif; ?>
									| <?php echo $this->reorderDate($content->date_created); ?>
									| Zobrazení: <?php echo $content->views; ?>
								</div>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php else: ?>
				<div class="alert alert-warning mt-3">
					<?php 
					if (!empty($this->text_search)) {
						echo JText::sprintf('ADSMANAGER_SELECT_CATEGORY_NO_RESULT', htmlspecialchars($this->text_search));
					} else {
						echo JText::_('ADSMANAGER_SELECT_CATEGORY_NO_RESULT');
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<style>
		.adsmanager_table_description {
			border-top: 1px solid #dee2e6;
			border-bottom: 1px solid #dee2e6;
			cursor: pointer;
		}
		.adsmanager_table_description:hover {
			background-color: #faf2cc;
		}
		.adsmanager_table_description td img {
			max-width: 100%;
			height: auto;
			display: block;
			margin-bottom: 10px;
			border-radius: 5px;
		}
		.juloawrapper h4 {
			font-size: 17.5px;
			margin-top:0;
		}
		</style>
		<?php
	}
	
	function reorderDate( $date ){
		$format = JText::_('ADSMANAGER_DATE_FORMAT_LC');
	
		if ($date && (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/",$date,$regs))) {
			$date = mktime( 0, 0, 0, $regs[2], $regs[3], $regs[1] );
			$date = $date > -1 ? strftime( $format, $date) : '-';
		}
		return $date;
	}
	
	function loadScriptImage($image_display)
	{
		$document = JFactory::getDocument();
		
		switch($image_display)
		{
			case 'jssor':
				$document->addScript(JURI::root().'components/com_adsmanager/js/flexslider/jquery.flexslider-min.js');
				$document->addStyleSheet(JURI::root().'components/com_adsmanager/js/flexslider/flexslider.css');
				break; 
			case 'popup':
				$document->addCustomTag('
				<script language="JavaScript" type="text/javascript">
				<!--
				function popup(img) {
				titre="Popup Image";
				titre="Agrandissement"; 
				w=open("","image","width=400,height=400,toolbar=no,scrollbars=no,resizable=no"); 
				w.document.write("<html><head><title>"+titre+"</title></head>"); 
				w.document.write("<script language=\"javascript\">function checksize() { if	(document.images[0].complete) {	window.resizeTo(document.images[0].width+10,document.images[0].height+50); window.focus();} else { setTimeout(\'checksize()\',250) }}</"+"script>"); 
				w.document.write("<body onload=\"checksize()\" leftMargin=0 topMargin=0 marginwidth=0 marginheight=0>");
				w.document.write("<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" height=\"100%\"><tr>");
				w.document.write("<td valign=\"middle\" align=\"center\"><img src=\""+img+"\" border=0 alt=\"Mon image\">"); 
				w.document.write("</td></tr></table>");
				w.document.write("</body></html>"); 
				w.document.close(); 
				} 
				
				-->
				</script>');
				break;
			case 'lightbox':
			case 'lytebox': 
 				$document->addCustomTag('<script type="text/javascript" src="'.$this->get("baseurl").'components/com_adsmanager/lytebox/js/lytebox_322cmod1.3.js"></script>'); 
 				$document->addCustomTag('<link rel="stylesheet" href="'.$this->get("baseurl").'/components/com_adsmanager/lytebox/css/lytebox_322cmod1.3.css" type="text/css" media="screen" />');
 				break; 
			case 'highslide': 
				$document->addCustomTag('<script type="text/javascript" src="'.$this->get("baseurl").'components/com_adsmanager/highslide/js/highslide-full.js"></script>'); 
				$document->addCustomTag('<script type="text/javascript">hs.graphicsDir = "'.$this->get("baseurl").'" + hs.graphicsDir;</script>'); 
				$document->addCustomTag('<link rel="stylesheet" href="'.$this->get("baseurl").'components/com_adsmanager/highslide/css/highslide-styles.css" type="text/css" media="screen" />'); 
				break; 
			default:
				break;
		}
	}
}
