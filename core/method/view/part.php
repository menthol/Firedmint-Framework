<?php
if (!defined('FM_SECURITY')) die();

function part($part,$data=array())
{
	$data += array('l10n'=>$this->data['l10n'],'controller'=>$this->data['controller'],'action'=>$this->data['action']);
	print '<?php $this->get(stripslashes("'.addslashes($part).'"),unserialize(stripslashes("'.addslashes(serialize($data)).'"))); ?>';

}