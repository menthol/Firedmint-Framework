<?php
if (!defined('FM_SECURITY')) die();

class FmHtml
{
	static $html = array('css'=>array(),'js'=>array(),'onload'=>array(),'script'=>array());
	
	static function factory()
	{
		foreach (FmConfig::$config['html']['js_autoload'] as $__key=>$__js)
			FmHtml::add('js',$__key,$__js);
	
		foreach (FmConfig::$config['html']['css_autoload'] as $__key=>$__css)
			FmHtml::add('css',$__key,$__css);
	}
	
	static function add($type,$key,$value)
	{
		FmHtml::$html[$type][$key] = $value;
	}
	
	static function get($type,$key)
	{
		if (isset(FmHtml::$html[$type][$key]))
			return FmHtml::$html[$type][$key];
	}
	
	static function delete($type,$key)
	{
		if (isset(FmHtml::$html[$type][$key]))
		{
			unset(FmHtml::$html[$type][$key]);
			return true;
		}
	}
	
	static function head()
	{
		echo '<?php FmHtml::head_dyn($view); ?>';
	}
	
	static function head_dyn($view)
	{
		foreach (FmHtml::$html['css'] as $css)
		{	
			if (is_string($file = _find("public/css/$css")))
				echo $view->select('snippet/css',array('path'=>$file));
			elseif (is_string($file = _find("public/$css")))
				echo $view->select('snippet/css',array('path'=>$file));
		}
		
		foreach (FmHtml::$html['js'] as $js)
		{	
			if (is_string($file = _find("public/js/$js")))
				echo $view->select('snippet/js',array('path'=>$file));
			elseif (is_string($file = _find("public/$js")))
				echo $view->select('snippet/js',array('path'=>$file));
		}
		
		$script = implode(PHP_EOL,FmHtml::$html['script']);
		
		foreach (FmHtml::$html['onload'] as $onload)
			$script .= $view->select('snippet/onload',array('script'=>$onload));
		
		echo $view->select('snippet/script',array('script'=>$script));
	}
}