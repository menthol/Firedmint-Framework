<?php 
if (!defined('FM_SECURITY')) die();
$lang = l10n::factory($view->data['l10n']);
?>
<ul id="menu">
	<li><a rel="start home" href="<?php print $view->uri(':',array('l10n'=>$view->data['l10n'])); ?>" title="<?php print fm::$config['site']['site-name']; ?>"><?php print $lang->get('homepage'); ?></a></li>
</ul>