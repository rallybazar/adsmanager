<?php
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');

$contentLink = JRoute::_('index.php?option=com_adsmanager&view=details&id='.$this->content->id.'&catid='.$this->content->catid);
?>

<div class="container my-4">
    <h2 class="mb-4"><?php echo JText::_('COM_ADSMANAGER_MAKE_PREMIUM_TITLE'); ?></h2>

    <p>
        <?php echo JText::sprintf(
            'COM_ADSMANAGER_MAKE_PREMIUM_TEXT', 
            $this->content->ad_headline, 
            '<a href="'.$contentLink.'">'.$contentLink.'</a>'
        ); ?>
    </p>

    <div class="premium-payment-instructions mt-3 p-3 border rounded bg-light">
        <h4><?php echo JText::_('COM_ADSMANAGER_PREMIUM_PAYMENT_TITLE'); ?></h4>
        <p>
            <?php 
            // Dynamicky vložíme ID inzerátu
            echo JText::sprintf(
                'COM_ADSMANAGER_PREMIUM_PAYMENT_INSTRUCTIONS', 
                $this->content->id
            ); 
            ?>
        </p>
        <ul>
            <li><?php echo JText::_('COM_ADSMANAGER_PREMIUM_PAYMENT_AMOUNT'); ?>: 4 €</li>
            <li><?php echo JText::_('COM_ADSMANAGER_PREMIUM_PAYMENT_IBAN'); ?>: SK13 0900 0000 0051 5719 4889</li>
            <li><?php echo JText::_('COM_ADSMANAGER_PREMIUM_PAYMENT_REFERENCE'); ?>: <?php echo (int)$this->content->id; ?></li>
        </ul>
    </div>

    <form action="<?php echo JRoute::_('index.php?option=com_adsmanager&task=sendpremiumrequest'); ?>" method="post" class="form-validate">
        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label"><?php echo JText::_('COM_ADSMANAGER_HEADLINE'); ?></label>
            <div class="col-sm-9">
                <input type="text" name="headline" value="<?php echo htmlspecialchars($this->content->ad_headline); ?>" class="form-control" readonly />
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label"><?php echo JText::_('COM_ADSMANAGER_DESCRIPTION'); ?></label>
            <div class="col-sm-9">
                <textarea 
                    name="description" 
                    rows="5" 
                    maxlength="200" 
                    placeholder="<?php echo JText::_('COM_ADSMANAGER_DESCRIPTION_PLACEHOLDER'); ?>" 
                    class="form-control"
                ><?php echo htmlspecialchars(mb_substr($this->content->ad_text, 0, 200)); ?></textarea>
            </div>
        </div>

        <div class="mb-3 row">
            <label class="col-sm-3 col-form-label"><?php echo JText::_('COM_ADSMANAGER_PRICE'); ?></label>
            <div class="col-sm-9">
                <input type="text" name="price" value="<?php echo htmlspecialchars($this->content->ad_price); ?>" class="form-control" readonly />
            </div>
        </div>
        <br>
        <div class="text-center mt-4">
            <?php
                echo '<button type="submit" class="btn btn-success mb-1">'.JText::_('COM_ADSMANAGER_SUBMIT').'</button>';
            ?>
        </div>

        <input type="hidden" name="adid" value="<?php echo (int)$this->content->id; ?>" />
        <input type="hidden" name="userid" value="<?php echo (int)$this->content->userid; ?>" />
        <input type="hidden" name="option" value="com_adsmanager" />
        <input type="hidden" name="task" value="sendpremiumrequest" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>
