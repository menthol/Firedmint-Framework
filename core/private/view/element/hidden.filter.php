<?php 
if (!defined('FM_SECURITY')) die();

return $view->select('form/hidden',array('*','input'=>
	'<input type="hidden"  name="'._attribute($view->data['element'])
	.'" id="'._attribute($view->data['id'])
	.'" value="'._attribute($view->data['value']).'"/>'.PHP_EOL
)); 

