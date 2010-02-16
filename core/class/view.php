<?php 
if (!defined('FM_SECURITY')) die();

function core_view_method_classBoot($fm)
{
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}");
}

function core_view_method_classConstruct($fm)
{
	return clone $fm;
}

function core_view_method_classStart($fm,$display_type,$view,$args,$cacheLifetime = null,$force_reload = false)
{
	if (is_null($cacheLifetime))
		$cacheLifetime = fm::$config['view']['cache'];
	
	$fm->cache = $cacheLifetime;
	$fm->force_reload = $force_reload;
	$fm->display_type = $display_type;
	if (is_array($view))
		$fm->view = $view;
	else
		$fm->view = array($view);
	$fm->cview = $fm;
	
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$display_type}"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$display_type}");
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$display_type}/".implode('_',$view).""))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$display_type}/".implode('_',$view)."");	
	$fm->cacheInclude('document',$args);	
}

function core_view_method_cacheInclude($fm,$part,$args)
{
	$view = clone $fm->cview;
	$view->part = $part;
	$view->as_cache = false;
	
	if (!is_dir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$view->display_type}/".implode('_',$view->view)."/{$view->part}"))
		mkdir(FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$view->display_type}/".implode('_',$view->view)."/{$view->part}");
	
	$args_key = sha1(serialize($args));
	$file_var = FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$view->display_type}/".implode('_',$view->view)."/{$view->part}/{$args_key}.vars".FM_PHP_EXTENSION;
	$file     = FM_PATH_VAR_PRIVATE.FM_SITE_DIR."{$fm->type}/{$view->display_type}/".implode('_',$view->view)."/{$view->part}/{$args_key}".FM_PHP_EXTENSION;
	
	$template = $view->find($view->part)->value;
	
	if (strlen($template)>0)
		$template_version = filemtime($template);
	else
		$template_version = null;
	
	$load_cache = false;
	
	if (is_file($file_var) && is_file($file) && $fm->force_reload==false)
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
}

function core_view_method_find($fm,$find)
{
	$path = array();
	foreach ($fm->view as $view)
	{
		$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_VIEW."$view/$find.{$fm->display_type}".FM_PHP_EXTENSION;
		$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_VIEW."$view/$find.{$fm->display_type}".FM_PHP_EXTENSION;
		if (strlen(fm::$config['view']['template']))
		{
			if (is_dir(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template']))
				$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$view/$find.{$fm->display_type}".FM_PHP_EXTENSION;
			else
				$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$view/$find.{$fm->display_type}".FM_PHP_EXTENSION;
		}
		foreach(fm::$core->extension as $data)
		{
			$path[] = $data['path'].FM_PATH_VIEW."$view/$find.{$fm->display_type}".FM_PHP_EXTENSION;
		}
		
	}
	
	$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_VIEW."$find.{$fm->display_type}".FM_PHP_EXTENSION;
	$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_VIEW."$find.{$fm->display_type}".FM_PHP_EXTENSION;
	if (strlen(fm::$config['view']['template']))
	{
		if (is_dir(FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template']))
			$path[] = FM_PATH_SITE.FM_SITE_DIR.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$find.{$fm->display_type}".FM_PHP_EXTENSION;
		else
			$path[] = FM_PATH_SITE.FM_PATH_SITE_ALL.FM_PATH_TEMPLATE.fm::$config['view']['template'].'/'."$find.{$fm->display_type}".FM_PHP_EXTENSION;
	}
	foreach(fm::$core->extension as $data)
	{
		$path[] = $data['path'].FM_PATH_VIEW."$find.{$fm->display_type}".FM_PHP_EXTENSION;
	}
	
	foreach ($fm->view as $view)
	{
		$path[] = FM_PATH_CORE.FM_PATH_VIEW."$view/$find.{$fm->display_type}".FM_PHP_EXTENSION;
	}
	
	$path[] = FM_PATH_CORE.FM_PATH_VIEW."$find.{$fm->display_type}".FM_PHP_EXTENSION;
	
	$return = clone $fm;
	foreach ($path as $file)
	{
		if (file_exists($file))
		{
			$return->value = $file;
			return $return;
		}
	}
}