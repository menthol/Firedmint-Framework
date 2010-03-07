<?php 
if (!defined('FM_SECURITY')) die();

if (isset($view->data['script']) && strlen($view->data['script'])>0) return '<script type="text/javascript">'.PHP_EOL.'<!--'.PHP_EOL.$view->data['script'].PHP_EOL.'//-->'.PHP_EOL.'</script>'.PHP_EOL;
return '';