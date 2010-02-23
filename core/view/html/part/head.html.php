<?php 
if (!defined('FM_SECURITY')) die();
?>
<title><?php $view->part('html/part/head-title',$this->data); ?></title>
<?php $view->part('html/part/head-meta',$this->data); ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print fm::$config['site']['charset']; ?>" />
<meta name="generator" content="Firedmint v<?php print FM_VERSION; ?>" />
<?php $view->getMeta();?>
