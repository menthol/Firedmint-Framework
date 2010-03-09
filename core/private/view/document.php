<?php 
if (!defined('FM_SECURITY')) die();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php l('xml-lang',true); ?>" lang="<?php l('xml-lang',true); ?>" dir="<?php l('lang-dir',true); ?>">
<head>
<?php $view->part('part/head'); ?>
<?php FmHtml::head(); ?>
<?php $view->virtual('head'); ?>
<?php $view->part('part/head-script'); ?>
<?php $view->part("head/$view->name"); ?>
</head>
<body class="<?php show($view->name); ?>">
<?php $view->part('layout/page'); ?>
</body>
</html>                                   