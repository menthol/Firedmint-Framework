<?php 
if (!defined('FM_SECURITY')) die();

return $view->select('form/button',array('*','input'=>
	'<input type="submit" class="'._attribute($view->data['class'])
	.'" name="'._attribute($view->data['element'])
	.'" title="'._attribute(_l($view->data['title']))
	.'" id="'._attribute($view->data['id'])
	.'" value="'._attribute(_l($view->data['label'])).'" />'.PHP_EOL
)); 
