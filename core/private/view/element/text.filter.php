<?php 
if (!defined('FM_SECURITY')) die();

return $view->select('form/line',array('*','input'=>
	'<input type="text" class="'._attribute($view->data['class'])
	.'" name="'._attribute($view->data['element'])
	.'" title="'._attribute(_l($view->data['title']))
	.'" id="'._attribute($view->data['id'])
	.'" value="'._attribute($view->data['value']).'"/>'.PHP_EOL
)); 

