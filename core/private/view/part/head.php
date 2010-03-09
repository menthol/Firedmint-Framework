<?php 
if (!defined('FM_SECURITY')) die();
?>
<title><?php part('part/head-title'); ?></title>
<?php part('part/head-meta'); ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php if (is_string(FmConfig::$config['header']['generator'])): ?><meta name="generator" content="<?php show(FmConfig::$config['header']['generator'],true); ?>" /><?php endif; ?>

