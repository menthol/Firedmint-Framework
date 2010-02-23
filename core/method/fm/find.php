<?php
if (!defined('FM_SECURITY')) die();

static function find($file)
{
	$file = trim($file);
	$return = fm::obj();
	$return->value = null;
	if (strlen($file)>0)
	{	
		if (file_exists(FM_PATH_SITE.FM_SITE_DIR.$file.FM_PHP_EXTENSION))
			$return->value = FM_PATH_SITE.FM_SITE_DIR.$file;
		elseif (file_exists(FM_PATH_SITE.FM_SITE_DIR.$file))
			$return->value = FM_PATH_SITE.FM_SITE_DIR.$file;
		elseif (file_exists(FM_PATH_SITE.FM_PATH_SITE_ALL.$file.FM_PHP_EXTENSION))
			$return->value = FM_PATH_SITE.FM_PATH_SITE_ALL.$file;
		elseif (file_exists(FM_PATH_SITE.FM_PATH_SITE_ALL.$file))
			$return->value = FM_PATH_SITE.FM_PATH_SITE_ALL.$file;
		else
		{
			foreach (fm::$extension as $extension)
			{
				if (file_exists($extension['path'].$file.FM_PHP_EXTENSION))
					$return->value = $extension['path'].$file;
				elseif (file_exists($extension['path'].$file))
					$return->value = $extension['path'].$file;
			}
			if ($return->value == null)
			{
				if (file_exists(FM_PATH_CORE.$file.FM_PHP_EXTENSION))
					$return->value = FM_PATH_CORE.$file;
				elseif (file_exists(FM_PATH_CORE.$file))
					$return->value = FM_PATH_CORE.$file;
			}
		}
	}
	if ($return->value == null)
		log::notice("Can't found $file");
	return $return;
}