<?php
/**
 * @package     AdsManager
 * @copyright   Copyright (C) 2010-2025 Juloa.com. All rights reserved.
 * @license     GNU/GPL
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$data = $app->getUserState('com_adsmanager.import_data');

JHTML::_('behavior.formvalidation');

// Zobrazenie Joomla flash správ
$messages = $app->getMessageQueue();
if (!empty($messages)) {
    echo '<div class="messages">';
    foreach ($messages as $msg) {
        $class = 'alert ';
        switch ($msg['type']) {
            case 'error':
                $class .= 'alert-danger';
                break;
            case 'warning':
                $class .= 'alert-warning';
                break;
            default:
                $class .= 'alert-success';
                break;
        }
        echo '<div class="' . $class . '">' . htmlspecialchars($msg['message']) . '</div>';
    }
    echo '</div>';
}
?>

<h2><?php echo JText::_('COM_ADSMANAGER_IMPORTAD_HEADER'); ?></h2>
<?php
// Predvyplnený defaultný JSON
$defaultJson = json_encode([
    "category" => "",
    "name" => "",
    "ad_city" => "",
    "ad_headline" => "",
    "ad_text" => "",
    "ad_price" => "",
    "ad_engine" => "",
    "email" => "",
    "ad_phone" => "",
    "images" => [""]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Získaj aktuálny JSON z user state alebo použij default
$currentJson = $app->getUserState('com_adsmanager.import_data_raw', $defaultJson);
?>

<?php
/**
 * Formuláre volajú úlohy (tasky) v kontroléri pomocou parametrov URL:
 * - option=com_adsmanager   => názov komponentu
 * - c=importad              => názov kontroléra (ImportadController)
 * - task=upload / createDraft => názov metódy v kontroléri, ktorá sa vykoná
 * 
 * Joomla podľa týchto parametrov vyberie správny kontrolér a zavolá metódu
 * upload() alebo createDraft() v triede AdsmanagerControllerImportad.
 * 
 * Preto je dôležité mať v URL parameter 'c' pre kontrolér a 'task' pre metódu.
 */
?>

<form action="<?php echo JRoute::_('index.php?option=com_adsmanager&c=importad&task=createDraft'); ?>" method="post">
    <label for="jsontext"><?php echo JText::_('COM_ADSMANAGER_IMPORTAD_ENTER_JSON'); ?></label><br />
    <textarea name="jsontext" id="jsontext" rows="15" cols="160" required><?php
        echo htmlspecialchars($currentJson, ENT_QUOTES, 'UTF-8');
    ?></textarea><br />
    <button type="submit" class="btn btn-success">
        <?php echo JText::_('COM_ADSMANAGER_IMPORTAD_CREATE_DRAFT'); ?>
    </button>
    <?php echo JHTML::_('form.token'); ?>
</form>

<hr>
<form action="<?php echo JRoute::_('index.php?option=com_adsmanager&c=importad&task=purgeExpired'); ?>" method="post">
    <button type="submit" class="btn btn-warning" 
        onclick="return confirm('<?php echo JText::_('COM_ADSMANAGER_IMPORTAD_CONFIRM_ARCHIVE'); ?>');">
        <?php echo JText::_('COM_ADSMANAGER_IMPORTAD_BUTTON_ARCHIVE'); ?>
    </button>
    <?php echo JHTML::_('form.token'); ?>
</form>



