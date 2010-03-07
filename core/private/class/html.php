<?php
if (!defined('FM_SECURITY')) die();

class html
{
	static $html = array('css'=>array(),'js'=>array(),'onload'=>array(),'script'=>array());
	
	static function factory()
	{
		foreach (config::$config['html']['js_autoload'] as $__key=>$__js)
			html::add('js',$__key,$__js);
	
		foreach (config::$config['html']['css_autoload'] as $__key=>$__css)
			html::add('css',$__key,$__css);
	}
	
	static function add($type,$key,$value)
	{
		html::$html[$type][$key] = $value;
	}
	
	static function get($type,$key)
	{
		if (isset(html::$html[$type][$key]))
			return html::$html[$type][$key];
	}
	
	static function delete($type,$key)
	{
		if (isset(html::$html[$type][$key]))
		{
			unset(html::$html[$type][$key]);
			return true;
		}
	}
	
	static function head()
	{
		echo '<?php html::head_dyn($view); ?>';
	}
	
	static function head_dyn($view)
	{
		foreach (html::$html['css'] as $css)
		{	
			if (is_string($file = _find("public/css/$css")))
				echo $view->select('snippet/css',array('path'=>$file));
			elseif (is_string($file = _find("public/$css")))
				echo $view->select('snippet/css',array('path'=>$file));
		}
		
		foreach (html::$html['js'] as $js)
		{	
			if (is_string($file = _find("public/js/$js")))
				echo $view->select('snippet/js',array('path'=>$file));
			elseif (is_string($file = _find("public/$js")))
				echo $view->select('snippet/js',array('path'=>$file));
		}
		
		$script = implode(PHP_EOL,html::$html['script']);
		
		foreach (html::$html['onload'] as $onload)
			$script .= $view->select('snippet/onload',array('script'=>$onload));
		
		echo $view->select('snippet/script',array('script'=>$script));
	}
}