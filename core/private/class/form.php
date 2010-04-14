<?php
if (!defined('FM_SECURITY')) die();

class form
{
	
	static $form = array();
	static $element_id = 0;
	
	const NONE        = 0;
	const LOADED      = 1;
	const POSTED      = 2;
	const PARTIAL     = 4;
	const VALIDATE    = 8;
	const FAILED      = 16;
	const PROCESS     = 32;
	const PROCESSED   = 64;
	
	static function factory()
	{
		if (isset($_POST['__formid']))
		{
			$formName = base64_decode($_POST['__formid']);
			if (!isset(form::$form[$formName]))
				form::getState($formName);
		}
	}
	
	static function getDefinition($formName)
	{
		$formName = strtolower($formName);
		
		if (isset(form::$form[$formName]))
			return form::$form[$formName];
			
		$formName = strtolower($formName);
		$__form = array();
		
		event::trigger('form',"def:$formName",'before',$__form);

		if($path = _find("private/form/$formName.definition.php"))
		{
			$form = array();
			include $path;
			$__form = array_replace_recursive($form,$__form);
		}
		
		$__form['element']['__formid'] = array('type' => 'hidden','value'=>base64_encode($formName),'required'=>true,);
		
		foreach (array_keys($__form['element']) as $element)
		{
			$__form['element'][$element] += array('element'=>$element,'type' => 'text','required' => false,'group' => null,'label' => "form:$element",'info'=>null,'validator' => array(),'value'=>null,'error'=>null,'option'=>array());
			$__form['element'][$element] += array('class'=>$__form['element'][$element]['type'],'title'=>$__form['element'][$element]['label']);
			if (!isset($__form['element'][$element]['id']) || empty($__form['element'][$element]['id'])) $__form['element'][$element]['id'] = $formName.'-'.$element.'-'.(++form::$element_id); 
			
			if (strlen($__form['element'][$element]['group'])>0 && (!isset($__form['group'][$__form['element'][$element]['group']]) || !is_array($__form['group'][$__form['element'][$element]['group']])))
				$__form['group'][$__form['element'][$element]['group']] = array('name' => $__form['element'][$element]['group'],'info'=>null,'legend'=>null);
		}
		
		
		if ($file = _find("private/form/$formName.class.php")) include_once $file;
		
		$className = "{$formName}Form";
		if (class_exists($className))
		{
			if (method_exists($className,'load'))
				$__form['loader'][][] = array($className,'load');
			if (method_exists($className,'validate'))
				$__form['validator'][][] = array($className,'validate');
			if (method_exists($className,'process'))
				$__form['processer'][][] = array($className,'process');
		}
		
		event::trigger('form',"def:$formName",'after',$__form);
		
		return $__form + array('loader' => array(),'validator' => array(),'processer' => array(),'element' => array(),'group'=>array(),'action'=>null,'onprocess'=>null,'static'=>false,'state'=>self::NONE,'error'=>array(),'html'=>null);
	}
	
	static function getState($formName)
	{
		$formName = strtolower($formName);
		if (isset(form::$form[$formName]) && form::$form[$formName]['state']>0)
			return form::$form[$formName]['state'];

		form::$form[$formName] = form::getDefinition($formName);
		form::$form[$formName]['state'] = self::LOADED;
		
		if (isset($_POST['__formid']) && $_POST['__formid']==base64_encode($formName))
		{
			form::setValues($formName,$_POST);
			form::$form[$formName]['state'] |= self::POSTED;
			
			foreach (form::$form[$formName]['element'] as $element=>$data)
				if (!isset($_POST[$element]) || (empty($_POST[$element]) && $data['required']==true))
					form::$form[$formName]['state'] |= self::PARTIAL;
			
			foreach (form::$form[$formName]['element'] as $element=>$data)
			{
				foreach ($data['validator'] as $validator)
				{
					$__callback  = array_shift($validator);
					array_unshift($validator,$formName,$element);
					if ((is_callable($callback = array('validator',$__callback)) || is_callable($callback = $__callback)) && !call_user_func_array($callback,$validator))
					{
						form::$form[$formName]['state'] |= self::FAILED;
					}
				}
			}

			foreach(form::$form[$formName]['validator'] as $validator)
			{
				$__callback  = array_shift($validator);
				array_unshift($validator,$formName);
				if ((is_callable($callback = array('validator',$__callback)) || is_callable($callback = $__callback)) && !call_user_func_array($callback,$validator))
				{	
					form::$form[$formName]['state'] |= self::FAILED;
				}
			}
			
			if (!(form::$form[$formName]['state'] & self::PARTIAL) && !(form::$form[$formName]['state'] & self::FAILED))
			{
				form::$form[$formName]['state'] |= self::PROCESS;
				foreach(form::$form[$formName]['processer'] as $processer)
				{
					$callback  = array_shift($processer);
					array_unshift($processer,$formName);
					if (is_callable($callback))
						call_user_func_array($callback,$processer);
				}
				form::$form[$formName]['state'] |= self::PROCESSED;
			}
		}
		
		return form::$form[$formName]['state'];
	}
	
	static function get($view,$formName)
	{
		$formName = strtolower($formName);

		if (form::getState($formName)==0)
			form::load($formName);
			
		if (!is_null(form::getHtml($formName)))
			echo form::getHtml($formName);
		elseif (is_string($return = $view->select("form/$formName",array('formName'=>$formName))))
			echo $return;
		else 
			echo $view->select('form/form',array('formName'=>$formName));
	}
	
	static function load($formName)
	{
		$formName = strtolower($formName);
		event::trigger('form',"load:$formName",'before');
		foreach(form::$form[$formName]['loader'] as $loader)
		{
			$callback  = array_shift($loader);
			array_unshift($loader,$formName);
			if (is_callable($callback))
				call_user_func_array($callback,$loader);
		}
		event::trigger('form',"load:$formName",'after');
	}
	
	static function setHtml($formName,$html)
	{
		form::$form[$formName]['html'] = $html;
	}
	
	static function getHtml($formName)
	{
		return form::$form[$formName]['html'];
	}
	
	
	static function getValue($formName,$element)
	{
		return form::$form[$formName]['element'][$element]['value'];
	}
	
	static function setValue($formName,$element,$value)
	{
		form::$form[$formName]['element'][$element]['value'] = $value;
	}
	
	static function setValues($formName,$values)
	{
		foreach ($values as $element=>$value)
			if (isset(form::$form[$formName]['element'][$element]))
				form::$form[$formName]['element'][$element]['value'] = $value;
	}
	
	static function getElement($formName,$element)
	{
		return form::$form[$formName]['element'][$element];
	}
	
	static function setElement($formName,$element,$data)
	{
		return form::$form[$formName]['element'][$element] = $data + form::$form[$formName]['element'][$element];
	}
	
	static function addElementError($formName,$element,$error)
	{
		form::$form[$formName]['element'][$element]['error'] = $error;
	}
	
	static function getElementError($formName,$element)
	{
		return form::$form[$formName]['element'][$element]['error'];
	}
	
	static function addError($formName,$error)
	{
		form::$form[$formName]['error'][] = $error;
	}
	
	static function getError($formName)
	{
		if (isset(form::$form[$formName]['error']))
			return form::$form[$formName]['error'];
		
		return array();
	}
	
	static function getGroupElements($formName,$group = null)
	{
		$formName = strtolower($formName);
		$elements = array();
		
		if (!isset(form::$form[$formName]))
			return array();
		
		if ($group==null || !isset(form::$form[$formName]['group'][$group]))
		{
			// no-group
			foreach (form::$form[$formName]['element'] as $element=>$definition)
				if (empty($definition['group']))
					$elements[$element] = $definition;
		}
		else
		{
			foreach (form::$form[$formName]['element'] as $element=>$definition)
				if ($definition['group']==$group)
					$elements[$element] = $definition;
		}
		return $elements;
	}
	
	static function getGroup($formName)
	{
		$formName = strtolower($formName);
		return array_keys(form::$form[$formName]['group']);
	}
	
	static function getGroupInfo($formName,$groupName)
	{
		$formName = strtolower($formName);
		if (isset(form::$form[$formName]['group'][$groupName]))
			return form::$form[$formName]['group'][$groupName];
	}
}