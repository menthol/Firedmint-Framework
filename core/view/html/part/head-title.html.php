<?php 
if (!defined('FM_SECURITY')) die();
?>
<?php print "{$view->data['controller']} - {$view->data['action']} - ".fm::$config['site']['site-name']; ?>