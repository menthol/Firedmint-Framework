<?php
if (!defined('FM_SECURITY')) die();

class FmForm
{
	static $form = array();
	static $element_id = 0;
	
	static function factory()
	{
		if (isset($_POST['__formid']))
		{
			$formName = base64_decode($_POST['__formid']);
			
			FmForm::$form[$formName] = FmForm::getDefinition($formName);
			FmForm::setValues($formName,$_POST);
			
			if (FmForm::getState($formName)==3)
			{
				foreach(FmForm::$form[$formName]['processer'] as $processer)
				{
					$callback  = array_shift($processer);
					array_unshift($processer,$formName);
					if (is_callable($callback))
						call_user_func_array($callback,$processer);
					
					
				}
			}
			FmForm::$form[$formName]['state'] = 4;
		}
	}
	
	static function getDefinition($formName)
	{
		$formName = strtolower($formName);
		
		if (isset(FmForm::$form[$formName]))
			return FmForm::$form[$formName];
			
		$formName = strtolower($formName);
		$__form = array();
		
		FmEvent::trigger('form',"def:$formName",'before',$__form);

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
			if (!isset($__form['element'][$element]['id']) || empty($__form['element'][$element]['id'])) $__form['element'][$element]['id'] = $formName.'-'.$element.'-'.(++FmForm::$element_id); 
			
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
		
		FmEvent::trigger('form',"def:$formName",'after',$__form);
		
		return $__form + array('loader' => array(),'validator' => array(),'processer' => array(),'element' => array(),'group'=>array(),'action'=>null,'onprocess'=>null,'static'=>false,'state'=>0,'error'=>array());
	}
	
	static function getState($formName)
	{
		/* 0 => new form
		 * 1 => partially posted
		 * 2 => fully posted but fail on validators
		 * 3 => fully posted and success on validators
		 * 4 => processed
		 */
		
		$formName = strtolower($formName);
		if (isset(FmForm::$form[$formName]) && FmForm::$form[$formName]['state']>0)
			return FmForm::$form[$formName]['state'];
		
		$state = 0;
		$definition = FmForm::getDefinition($formName);
		
		if (isset($_POST['__formid']) && $_POST['__formid']==base64_encode($formName))
		{
			$state = 2;
			foreach ($definition['element'] as $element=>$data)
				if (!isset($_POST[$element]) || (empty($_POST[$element]) && $data['required']==true))
					$state = 1;
			
			
			if ($state == 2) $state = 3;
			foreach ($definition['element'] as $element=>$data)
			{
				foreach ($data['validator'] as $validator)
				{
					$__callback  = array_shift($validator);
					array_unshift($validator,$formName,$element);
					if ((is_callable($callback = array('validator',$__callback)) || is_callable($callback = $__callback)) && !call_user_func_array($callback,$validator) && $state==3)
						$state = 2;
				}
			}
			
			foreach($definition['validator'] as $validator)
			{
				$__callback  = array_shift($validator);
				array_unshift($validator,$formName);
				if ((is_callable($callback = array('validator',$__callback)) || is_callable($callback = $__callback)) && !call_user_func_array($callback,$validator) && $state==3)
					$state = 2;
			}
		}
		$definition['state'] = $state;
		FmForm::$form[$formName]= $definition;
		return $state;
	}
	
	static function load($formName)
	{
		$formName = strtolower($formName);
		FmEvent::trigger('form',"load:$formName",'before');
		foreach(FmForm::$form[$formName]['loader'] as $loader)
		{
			$callback  = array_shift($loader);
			array_unshift($loader,$formName);
			if (is_callable($callback))
				call_user_func_array($callback,$loader);
		}
		FmEvent::trigger('form',"load:$formName",'after');
	}
	
	static function getValue($formName,$element)
	{
		return FmForm::$form[$formName]['element'][$element]['value'];
	}
	
	static function setValue($formName,$element,$value)
	{
		FmForm::$form[$formName]['element'][$element]['value'] = $value;
	}
	
	static function setValues($formName,$values)
	{
		foreach ($values as $element=>$value)
			if (isset(FmForm::$form[$formName]['element'][$element]))
				FmForm::$form[$formName]['element'][$element]['value'] = $value;
	}
	
	static function getElement($formName,$element)
	{
		return FmForm::$form[$formName]['element'][$element];
	}
	
	static function setElement($formName,$element,$data)
	{
		return FmForm::$form[$formName]['element'][$element] = $data + FmForm::$form[$formName]['element'][$element];
	}
	
	static function addError($formName,$error)
	{
		FmForm::$form[$formName]['element'][$element]['value'] = $value;
	}
	
	static function get($view,$formName)
	{
		$formName = strtolower($formName);

		if (FmForm::getState($formName)==0)
			FmForm::load($formName);
		
		if (is_string($return = $view->select("form/$formName",array('formName'=>$formName))))
			echo $return;
		else 
			echo $view->select('form/form',array('formName'=>$formName));
	}
	
	static function getGroupElements($formName,$group = null)
	{
		$formName = strtolower($formName);
		$elements = array();
		
		if (!isset(FmForm::$form[$formName]))
			return array();
		
		if ($group==null || !isset(FmForm::$form[$formName]['group'][$group]))
		{
			// no-group
			foreach (FmForm::$form[$formName]['element'] as $element=>$definition)
				if (empty($definition['group']))
					$elements[$element] = $definition;
		}
		else
		{
			foreach (FmForm::$form[$formName]['element'] as $element=>$definition)
				if ($definition['group']==$group)
					$elements[$element] = $definition;
		}
		
		return $elements;
	}
	
	static function getGroup($formName)
	{
		$formName = strtolower($formName);
		return array_keys(FmForm::$form[$formName]['group']);
	}
	
	static function getGroupInfo($formName,$groupName)
	{
		$formName = strtolower($formName);
		if (isset(FmForm::$form[$formName]['group'][$groupName]))
			return FmForm::$form[$formName]['group'][$groupName];
	}
}