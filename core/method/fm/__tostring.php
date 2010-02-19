<?php
if (!defined('FM_SECURITY')) die();

function __toString()
{
	return "{$this->toString()->value}";
}