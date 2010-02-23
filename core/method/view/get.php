<?php 
if (!defined('FM_SECURITY')) die();

function get($part,$data = null)
{
	if (strlen($part)==0)
	{
		return $this;
	}
	if (!is_array($data))
	{
		$data = $this->data;
	}
	
	if (array_key_exists('l10n',$data))
	{
		$data += array('l10n'=>$data['l10n']);
	}
	elseif (array_key_exists('l10n',$this->data))
	{
		$data += array('l10n'=>$this->data['l10n']);
	}
	else
	{
		$data += array('l10n'=>fm::$config['l10n']['local']);
	}
	
	$view = view::factory($this->display_type,$this->view,$data,$this->cache,$this->force_reload);
	
	$view->part = $part;
	$view->as_cache = false;
	
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/".implode('.',explode('/',$view->part))))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/".implode('.',explode('/',$view->part)));
	
	$args_key = sha1(serialize($data));
	$file_var = FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/".implode('.',explode('/',$view->part))."/{$args_key}.vars".FM_PHP_EXTENSION;
	$file     = FM_PATH_VAR_PRIVATE.FM_SITE_DIR."view/{$view->display_type}/".implode('_',$view->view)."/".implode('.',explode('/',$view->part))."/{$args_key}".FM_PHP_EXTENSION;
	
	$template = $view->findView($view->part)->value;
	
	if (strlen($template)==0)
	{	
		log::error('Part no found : '.$view->part);
		return $this;
	}	
	$template_version = filemtime($template);
	
	$load_cache = false;
	
	if (is_file($file_var) && is_file($file) && $this->force_reload==false)
	{
		$view->as_cache = true;
		$v = array();
		include $file_var;
		
		if ((array_key_exists('valid_until',$v) && (($v['valid_until']>time() && $v['template_version']==$template_version))))
			$load_cache = true;
	}
	
	if (!$load_cache)
	{
		$view->is_valid = true;
		
		ob_start();
		
		include $template;
		$out = @ob_get_contents();
		
		ob_end_clean();
		
		$load_cache = true;
		if (!$view->is_valid && !$view->as_cache)
		{
			$load_cache = false;
		}	
		
		if ($view->is_valid)
		{
			@unlink($file);
			@unlink($file_var);
			
			$out = '<?php '.PHP_EOL.'if (!defined(\'FM_SECURITY\')) die();'.PHP_EOL.'?>'.$out;
			
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
	
	if ($load_cache)
		include $file;
	
	return $this;
}
