<?php 
if (!defined('FM_SECURITY')) die();

class modelMagic
{
	private $_connexion;
	private $_table;
	private $_currentSelect;
		
	function setModel($table)
	{
		$this->_connexion = model::getModelConnexion($table);
		$this->_table     = $table;
	}
	
	function first()
	{
		return $this->_connexion->selectFetch($this->_table,$this->getValues(),1,0);
	}
	
	function all()
	{
		return $this->_connexion->selectFetchAll($this->_table,$this->getValues());
	}
	
	function update($data)
	{
		return $this->_connexion->update($this->_table,$data,$this->getValues());
	}
	
	function insert()
	{
		return $this->_connexion->insert($this->_table,$this->getValues());
	}
	
	function delete()
	{
		return $this->_connexion->delete($this->_table,$this->getValues());
	}
	
	private function getValues()
	{
		$fields = array();
		foreach ($this as $field=>$value)
		{
			if ($field[0]!='_')
			{
				$__sign = null;
				$__escape = true;
				$__value = null;
				
				if (is_array($value))
				{
					foreach ($value as $val)
					{
						if (is_string($val) && is_null($__sign) && in_array($val,$this->_connexion->getOperators()))
						{
							$__sign = $val;
						}
						elseif ($val===true)
						{
							$__escape = true;
						}
						elseif ($val===false)
						{
							$__escape = false;
						}
						else
						{
							$__value = $val;
						}
					}
				}
				else
				{
					$__value = $value;
				}
				
				if (is_null($__sign))
					$__sign = '=';
				
				$fields[$field] = array('sign'=>$__sign,'escape'=>$__escape,'value'=>$__value);
			}
		}

		return $fields;
	}
}
