<?php 
if (!defined('FM_SECURITY')) die();

function get($part,$argument = null)
{
	if (!is_array($argument))
	{
		$argument = $this->argument;
	}
	
	if (array_key_exists('l10n',$argument))
	{
		$argument += array('l10n'=>$argument['l10n']);
	}
	elseif (array_key_exists('l10n',$this->argument))
	{
		$argument += array('l10n'=>$this->argument['l10n']);
	}
	else
	{
		$argument += array('l10n'=>fm::$config['l10n']['local']);
	}
	
	$view = view::factory($this->display_type,$this->view,$argument,$this->cache,$this->force_reload);
	
	$view->part = $part;
	$view->as_cache = false;
	
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/{$view->part}"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/{$view->part}");
	
	$args_key = sha1(serialize($argument));
	$file_var = FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/{$view->part}/{$args_key}.vars".FM_PHP_EXTENSION;
	$file     = FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/{$view->part}/{$args_key}".FM_PHP_EXTENSION;
	
	$template = view::findView($view->part)->value;
	
	#############################
	if (strlen($template)>0)
		$template_version = filemtime($template);
	else
		$template_version = null;
	
	$load_cache = false;
	
	if (is_file($file_var) && is_file($file) && $this->force_reload==false)
	{
		$view->as_cache = true;
		$v = array();
		include $file_var;
		
		if ((array_key_exists('valid_until',$v) && (($v['valid_until']>time() && $v['template_version']==$template_version))) || strlen($template)==0)
			$load_cache = true;
	}
	
	if (!$load_cache)
	{
		$view->is_valid = true;
		
		@ob_clean();
		foreach ($argument as $var=>$arg)
		{
			if (is_a($arg,'fm'))
				$$var = $arg;
			else
			{
				$$var = fm::factory();
				$$var->value = $arg;
			}
		}	
		
		include $template;
		$out = @ob_get_contents();
		
		$load_cache = true;
		if (!$view->is_valid && !$view->as_cache)
		{
			$load_cache = false;
		}	
		
		if ($view->is_valid)
		{
			@unlink($file);
			@unlink($file_var);
			
			@file_put_contents($file,trim($out));
			
			$valid_until = time() + $view->cache;
			$build_date = time();
			$out = <<<EOL
<?php 
\$v['build_date'] = '$build_date';
\$v['valid_until'] = '$valid_until';
\$v['template_version'] = '$template_version';
EOL;
			@file_put_contents($file_var,$out);	
		}
	}
	
	@ob_end_clean();
	
	if ($load_cache)
		include $file;
	
	return $this;
}
