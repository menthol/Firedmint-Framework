<?php 
if (!defined('FM_SECURITY')) die();

return $view->select('form/bloc',array('*','input'=>
	'<textarea class="'._attribute($view->data['class'])
	.'" name="'._attribute($view->data['element'])
	.'" cols="'._attribute(isset($view->data['option']['cols'])?$view->data['option']['cols']:25)
	.'" rows="'._attribute(isset($view->data['option']['rows'])?$view->data['option']['rows']:5)
	.'" id="'._attribute($view->data['id'])
	.'">'.$view->data['value'].'</textarea>'
)); 
