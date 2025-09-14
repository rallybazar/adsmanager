<?php
/**
 * @package		AdsManager
 * @copyright	Copyright (C) 2010-2014 Juloa.com. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.view');
require_once(JPATH_BASE."/components/com_adsmanager/helpers/general.php");

/**
 * @package		Joomla
 * @subpackage	Contacts
 */  
class AdsmanagerViewFront extends TView
{
	function display($tpl = null)
	{
		jimport( 'joomla.session.session' );	
		$currentSession = JSession::getInstance('none',array());
		$currentSession->set("search_fields","");
		$currentSession->set("searchfieldscatid",0);
		$currentSession->set("searchfieldssql"," 1 ");
		$currentSession->set("tsearch","",'adsmanager');
		
		$app	= JFactory::getApplication();
		$pathway = $app->getPathway();
		

		$user		= JFactory::getUser();
		
		$document	= JFactory::getDocument();
		
		$contentmodel	=$this->getModel( "content" );
		$catmodel	=$this->getModel( "category" );

		// Get the parameters of the active menu item
		$menus	= $app->getMenu();
		$menu    = $menus->getActive();
		
		$conf = TConf::getConfig();
		
		$rootid = JRequest::getInt('rootid',0);
		
		$cats = $catmodel->getFlatTree(true, true, $nbContents, 'read',$rootid);
        
		$this->assignRef('cats',$cats);
		$this->assignRef('conf',$conf);
		
		$document->setTitle( JText::_('ADSMANAGER_PAGE_TITLE_PREVIEW'));
		
		$general = new JHTMLAdsmanagerGeneral(0,$conf,$user);
		$this->assignRef('general',$general);
		
		$nbimages = $conf->nb_images;
		if (function_exists("getMaxPaidSystemImages"))
		{
			$nbimages += getMaxPaidSystemImages();
		}
		$this->assignRef('nbimages',$nbimages);
		
		$fieldmodel		= $this->getModel("field");
		$field_values = $fieldmodel->getFieldValues();
		$plugins = $fieldmodel->getPlugins();
		$field = new JHTMLAdsmanagerField($conf,$field_values,1,$plugins);
		$this->assignRef('field',$field);
		
		$fields = $fieldmodel->getFields(true,null,null,"fieldid","ASC",true,'write');
		$this->assignRef('fields',$fields);
		
		$nb_cols = $conf->nb_last_cols;
		$nb_rows = $conf->nb_last_rows;
		$contents = $contentmodel->getLatestContents($nb_cols*$nb_rows,0,"no",$rootid);
		$this->assignRef('contents',$contents);

		parent::display($tpl);
	}
	
	function displayContents($contents,$nbimages) {
		$conf = TConf::getConfig();
	?>
		<h1 class="contentheading"><?php echo JText::_('ADSMANAGER_LAST_ADS');?></h1>
		<div class='adsmanager_box_module' align="center">
			<table class='adsmanager_inner_box' width="100%">
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
					
				echo "<b><a href='$linkTarget'>".$row->ad_headline."</a></b>"; 
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
			<h4 class="mb-3 text-dark"><?php echo JText::_('ADSMANAGER_LAST_ADS'); ?></h4>

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
									<?php if (!empty($content->ad_price)) echo htmlspecialchars($content->ad_price) . " €<br/>"; ?>
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
}
