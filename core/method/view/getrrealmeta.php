<?php 
if (!defined('FM_SECURITY')) die();

public function getRealMeta()
{
	foreach (html::$css as $file=>$media)
	{
		$path = $this->uri('@'.FM_PATH_PUBLIC.$file.'.css')->value;
		if (strlen($path)>0)
		{
			print "<link rel=\"stylesheet\" href=\"{$path}\"  media=\"".implode(',',array_keys($media))."\"/>".PHP_EOL;
		}
	}
	
	foreach (html::$js as $file)
	{
		$path = $this->uri('@'.FM_PATH_PUBLIC.$file.'.js')->value;
		if (strlen($path)>0)
		{
			print "<script src=\"{$path}\" type=\"text/javascript\"></script>".PHP_EOL;
		}
	}

	foreach (html::$head as $head)
	{
		print $head.PHP_EOL;
	}
	
} 