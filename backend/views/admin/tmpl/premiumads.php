<?php
defined('_JEXEC') or die;

// $this->premiumAds a $this->pagination musia byť nastavené vo view
?>
<div class="container">
    <h2><?php echo JText::_('COM_ADSMANAGER_PREMIUM_ADS'); ?></h2>

    <?php if (!empty($this->premiumAds)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th><?php echo JText::_('ID'); ?></th>
                    <th><?php echo JText::_('Headline'); ?></th>
                    <th><?php echo JText::_('URL'); ?></th>
                    <th><?php echo JText::_('Published'); ?></th>
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
                        <td><a href="<?php echo htmlspecialchars($ad->url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank"><?php echo htmlspecialchars($ad->url, ENT_QUOTES, 'UTF-8'); ?></a></td>
                        <td><?php echo $ad->published ? JText::_('Yes') : JText::_('No'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php echo $this->pagination ? $this->pagination->getListFooter() : ''; ?>

    <?php else: ?>
        <p><?php echo JText::_('COM_ADSMANAGER_NO_PREMIUM_ADS'); ?></p>
    <?php endif; ?>
</div>
