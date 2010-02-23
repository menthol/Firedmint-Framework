<?php 
if (!defined('FM_SECURITY')) die();
$lang = l10n::factory($view->data['l10n']);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $lang->get('xml_lang'); ?>" lang="<?php print $lang->get('xml_lang'); ?>" dir="<?php print $lang->get('lang_dir'); ?>">
<head>
<?php $view->part('html/part/head',$this->data);?>
<?php $view->part('html/part/head-special',$this->data);?>
</head>
<body class="<?php print "{$view->data['controller']} {$view->data['controller']}_{$view->data['action']} {$view->data['action']}"; ?>">
<?php $view->part('html/body',$this->data);?>
<?php print "<?php print_r(fm::\$config['tmp']); print \"\r\n\r\n\r\n Généré en \".(microtime(true)-FM_START_TIME).\" sec \r\n\"; ?>"; ?>
</body>
</html>                                               