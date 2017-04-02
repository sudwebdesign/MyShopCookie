<?php
/**
 * Plugin MyShopCookie
 *
 * @depend de plxMyShop v0.11b 
 * @version 0.2
 * @date 02.13.2017
 * @author SudWebDesign.fr
 **/
if(!defined('PLX_ROOT')) exit; 
 
# Control du token du formulaire
plxToken::validateFormToken($_POST);

if(!empty($_POST)) {
 $plxPlugin->setParam('localStorage', $_POST['localStorage'], 'numeric');
 $plxPlugin->setParam('cookie', ($_POST['localStorage']!='1'?'0':$_POST['cookie']), 'numeric');
 $plxPlugin->saveParams();
 header('Location: parametres_plugin.php?p=MyShopCookie');
 exit;
}
$parms = array();
$parms['localStorage'] = $plxPlugin->getParam('localStorage')!='' ? $plxPlugin->getParam('localStorage') : '1';
$parms['cookie'] = $plxPlugin->getParam('cookie')!='' ? $plxPlugin->getParam('cookie') : '1';
if (!defined('PLX_VERSION')) // avant 5.5
 if ($plxAdmin->version != '5.4') echo '<h2>' . get_class($plxPlugin) . '</h2>'; // avant 5.4 display title
?>
<p><?php $plxPlugin->lang('L_CONFIG') ?></p>
<form action="parametres_plugin.php?p=MyShopCookie" method="post">
 <fieldset>
  <p><?php $plxPlugin->lang('L_CONFIG_LOCALSTORAGE') ?></p>
  <?php plxUtils::printSelect('localStorage', array('1'=>L_YES,'0'=>L_NO),$parms['localStorage']); ?>
  <p><?php echo $plxPlugin->getLang('L_CONFIG_COOKIE') ?></p>
  <?php plxUtils::printSelect('cookie', array('1'=>L_YES,'0'=>L_NO),$parms['cookie']); ?>
 </fieldset>
 <?php echo plxToken::getTokenPostMethod() ?>
 <p class="in-action-bar">
  <input type="submit" name="submit" value="<?php echo $plxPlugin->getLang('L_SAVE') ?>" />
 </p>
</form>
