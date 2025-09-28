<?php
defined('_JEXEC') or die;

// $this->premiumAds a $this->pagination musia byť nastavené vo view

?>
<div class="container">
    <h2><?php echo JText::_('COM_ADSMANAGER_PREMIUM_ADS'); ?></h2>
    <!-- Tlačidlo Nový inzerát -->
    <div class="mb-3">
        <a href="<?php echo JRoute::_('index.php?option=com_adsmanager&c=premiumads&task=add'); ?>" class="btn btn-success">
            <span class="icon-new" aria-hidden="true"></span>
            <?php echo JText::_('COM_ADSMANAGER_NEW_PREMIUM_AD'); ?>
        </a>
    </div>

    <?php if (!empty($this->premiumAds)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo JText::_('ID'); ?></th>
                    <th><?php echo JText::_('Headline'); ?></th>
                    <th><?php echo JText::_('COM_ADSMANAGER_DATE_CREATED'); ?></th>
                    <th><?php echo JText::_('COM_ADSMANAGER_PUBLISHED'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->premiumAds as $ad): ?>
                    <tr>
                        <td><?php echo (int)$ad->id; ?></td>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_adsmanager&c=premiumads&task=edit&id=' . (int)$ad->id); ?>">
                                <?php echo htmlspecialchars($ad->headline, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                        <td><?php echo !empty($ad->date_created) ? JHtml::_('date', $ad->date_created, JText::_('DATE_FORMAT_LC2')) : '-'; ?></td>
                        <td><?php echo $ad->published ? JText::_('JPUBLISHED') : JText::_('JUNPUBLISHED'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo $this->pagination ? $this->pagination->getListFooter() : ''; ?>

    <?php else: ?>
        <p><?php echo JText::_('COM_ADSMANAGER_NO_PREMIUM_ADS'); ?></p>
    <?php endif; ?>
</div>
