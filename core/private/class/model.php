<?php 
if (!defined('FM_SECURITY')) die();

class model
{
	static $connexion = array();
	
	static $db = array();
	
	static $model = array();
	
	static $map = array();
	
	function __construct()
	{
		$__db = array();
		$__model = array();
		$__map = array();
		foreach (_getPaths('private/model.php') as $file)
		{
			$db = array();
			$model = array();
			$map = array();
			include $file;
			$__db = array_replace_recursive($db,$__db);
			$__model = array_replace_recursive($model,$__model);
			$__map = array_replace_recursive($map,$__map);
		}
		model::$db = $__db;
		model::$model = $__model;
		model::$map = $__map;
	}
	
	static function getConnexion($connexion = null)
	{
		if (is_null($connexion))
			$connexion = config::$config['model']['default_connexion'];
		
		if (!isset(model::$connexion[$connexion]))
		{
			if (!isset(model::$db[$connexion]))
			{
				log::error("[model] Connexion $connexion not found");
				return _subClass('driver','emptyDriver');
			}
			else
			{
				$driver = model::$db[$connexion]['driver'].'Driver';
				
				model::$connexion[$connexion] = _subClass('driver',$driver);
				
				model::$connexion[$connexion]->config(model::$db[$connexion]);
			}
		}
		
		return model::$connexion[$connexion];
	}
	
	static function getModelConnexion($model)
	{
		if (isset(model::$model[$model]))
			return model::getConnexion(model::$model[$model]);

		return model::getConnexion();
	}
}
