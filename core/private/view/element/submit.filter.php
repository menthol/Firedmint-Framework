<?php 
if (!defined('FM_SECURITY')) die();

return $view->select('form/button',array('*','input'=>
	'<input type="submit" class="'._attribute($view->data['class'])
	.'" name="'._attribute($view->data['element'])
	.'" title="'._attribute(_t($view->l10n,$view->data['title']))
	.'" id="'._attribute($view->data['id'])
	.'" value="'._attribute(_t($view->l10n,$view->data['label'])).'" />'.PHP_EOL
)); 
