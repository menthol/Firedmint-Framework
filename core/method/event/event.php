<?php
if (!defined('FM_SECURITY')) die();

function event($event,$event_part=null)
{
	$function_prefix = array();
	$function_prefix[] = "site";
	$function_prefix[] = "all";
	foreach (fm::$extension as $extension=>$data)
		$function_prefix[] = $extension;
	$function_prefix[] = "core";
	
	$event = trim(strtolower($event));
	
	if (array_key_exists($event,event::$event) && is_array(event::$event[$event]))
	{
		foreach (array('before','main','after') as $event_part_tmp)
		{
			if ((array_key_exists($event_part_tmp,event::$event[$event])
				&& is_array(event::$event[$event][$event_part_tmp]))
				&& ($event_part==null || $event_part==$event_part_tmp))
			{
				foreach (event::$event[$event][$event_part_tmp] as $callback=>$arguments)
				{
					$is_find = false;
					foreach ($function_prefix as $prefix)
					{
						if (!$is_find)
						{
							if (function_exists("{$prefix}_event_callback_{$callback}"))
							{
								$is_find = true;
								array_unshift($arguments,$event);
								array_unshift($arguments,&$this);
								$tmp_return = call_user_func_array("{$prefix}_event_callback_{$callback}",$arguments);
					
								if (!(is_a($tmp_return,'fm')))
								{
									if (!is_null($tmp_return))
										$this->value = $tmp_return;
								}
								else
									return $tmp_return;
							}
						}
					}
				}
			}
		}
	}
	
	return $this;
}
