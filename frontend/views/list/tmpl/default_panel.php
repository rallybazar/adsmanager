<?php
/**
 * @package     AdsManager
 * @copyright   Copyright (C) 2010-2014 Juloa.com
 * @license     GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="container-fluid">
    <?php if (!empty($this->premiumAds)): ?>
        <table class="table w-100">
            <tbody>
            <?php foreach($this->premiumAds as $ad): 
                $linkTarget = !empty($ad->url) 
                    ? $ad->url 
                    : TRoute::_("index.php?option=com_adsmanager&view=details&id=".$ad->id."&catid=".$ad->catid);
            ?>
                <tr class="adsmanager_table_description premium-ad" 
                    onclick="window.location='<?php echo $linkTarget; ?>'">

                    <!-- Ľavý stĺpec: obrázok -->
                    <td style="width: 35%; vertical-align: top; padding: 15px; text-align:center;">
                        <?php
                        // Zistíme, či máme platnú URL obrázku
                        $imgSrc = !empty($ad->image) ? htmlspecialchars($ad->image) : ADSMANAGER_NOPIC_IMG;
                        ?>
                        <a href="<?php echo $linkTarget; ?>">
                            <img class="fad-image img-fluid rounded border border-warning"
                                src="<?php echo $imgSrc; ?>"
                                alt="<?php echo htmlspecialchars($ad->headline ?: 'nopic'); ?>" />
                        </a>
                    </td>

                    <!-- Pravý stĺpec -->
                    <td style="width: 65%; vertical-align: top; padding: 15px;">
                        <div style="display:flex; flex-direction:column;">

                            <!-- Nadpis s badge -->
                            <h4 class="fw-bold mb-2 text-dark">
                                <a href="<?php echo $linkTarget; ?>" class="text-dark">
                                    <b><?php echo htmlspecialchars($ad->headline); ?></b>
                                </a>
                                <span class="badge bg-warning text-dark ms-2">PREMIUM</span>
                            </h4>

                            <!-- Popis -->
                            <?php if (!empty($ad->description)): ?>
                                <div class="mb-2 text-muted"><?php echo nl2br(htmlspecialchars($ad->description)); ?></div>
                            <?php endif; ?>

                            <!-- Dátum + views -->
                            <div class="text-dark">
                                <span><?php echo $this->reorderDate($ad->date_created); ?></span>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Normálne inzeráty -->
    <?php if (!empty($this->contents)): ?>
        <table class="table w-100">
            <tbody>
            <?php foreach($this->contents as $content): 
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

                            <!-- Nadpis s linkom BOLD, margin-top 0 -->
                            <h4 class="fw-bold mb-2 text-dark juloawrapper" style="margin-top:0;">
                                <a href="<?php echo $linkTarget; ?>" class="text-dark">
                                    <b><?php echo $content->ad_headline; ?></b>
                                </a>
                            </h4>

                            <!-- Dynamické polia bez BOLD -->
                            <?php foreach($this->columns as $col): ?>
                                <div class="mb-1 text-dark column_<?php echo $col->id; ?>">
                                    <?php 
                                    if (isset($this->fColumns[$col->id])) {
                                        $price = '';
                                        $priceOther = '';
                                        $otherLines = [];

                                        // iterujeme cez všetky polia a ukladáme hodnoty
                                        foreach($this->fColumns[$col->id] as $field) {
                                            $c = $this->field->showFieldValue($content, $field); 

                                            // iba ak hodnota existuje a nie je prázdna
                                            if ($c !== null && trim($c) !== '') {
                                                $fieldName = $field->name;

                                                // spracovanie ceny a alternatívnej ceny
                                                if ($fieldName == 'ad_price') {
                                                    $price = $c;
                                                } elseif ($fieldName == 'ad_priceother') {
                                                    $priceOther = $c;
                                                } else {
                                                    $otherLines[] = $c;
                                                }
                                            }
                                        }

                                        // vytvorenie riadku s cenou
                                        $priceLine = '';
                                        if ($price !== '' && $priceOther !== '') {
                                            $priceLine = $price . " | " . $priceOther;
                                        } elseif ($price !== '') {
                                            $priceLine = $price;
                                        } elseif ($priceOther !== '') {
                                            $priceLine = $priceOther;
                                        }

                                        // vypíšeme cenu / alternatívnu cenu
                                        if ($priceLine !== '') echo $priceLine . "<br/>";

                                        // vypíšeme ostatné polia
                                        foreach($otherLines as $line){
                                            echo $line . "<br/>";
                                        }
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>

                            <!-- Info: užívateľ, dátum, views, NEW/HOT, favorite -->
                            <div class="mt-2 text-dark">
                                <?php 
                                if($content->userid != 0){
                                    $target = TLink::getUserAdsLink($content->userid);
                                    $authorName = $this->conf->display_fullname == 1 
                                        ? $content->fullname 
                                        : $content->user;

                                    // Iba meno autora tučné
                                    echo JText::_('ADSMANAGER_FROM') . " <a href='".$target."' class='text-dark'><b>".$authorName."</b></a>";
                                    echo " | ";
                                }
                                ?>
                                <span><?php echo $this->reorderDate($content->date_created); ?></span>
                                | <span><?php echo sprintf(JText::_('ADSMANAGER_VIEWS'),$content->views); ?></span>

                                <?php if ($this->conf->show_new && $this->isNewcontent($content->date_created,$this->conf->nbdays_new)): ?>
                                    | <span class="badge bg-success">NEW</span>
                                <?php endif; ?>

                                <?php if ($this->conf->show_hot && $content->views >= $this->conf->nbhits): ?>
                                    | <span class="badge bg-danger">HOT</span>
                                <?php endif; ?>

                                <?php if ($this->conf->show_favorite): ?>
                                    | <span class="favorite_ads" id="fav_<?php echo $content->id; ?>">
                                        <?php echo in_array($content->id,$this->favorites) 
                                            ? JText::_('ADSMANAGER_REMOVE_FAVORITE') 
                                            : JText::_('ADSMANAGER_ADD_FAVORITE'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.premium-ad {
    background-color: #fff8e1;
}
.premium-ad:hover {
    background-color: #ffecb3;
}
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
}
.juloawrapper h4 {
    font-size: 17.5px;
    margin-top:0; /* zarovnanie s hornou hranou obrázka */
}
.mb-3 h2 {
    font-weight: normal;
    margin-top: 0; /* voliteľné, ak chceš zarovnať k obsahu */
}
</style>
