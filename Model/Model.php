<?php
namespace Model;

class Settings { 
	public static $usePrimitives = false;
}

class _Registered {
	public static $single;
	public static $plural;
}

function register($name, $class = false) {
	$class = $class ? $class : ("\Model\\" . $name);

	foreach(get_class_vars($class) as $var => $type) {
		if(!defined($var)) {
			define('Model\\' . $var, $var);
		}
	}

	_Registered::$single[$name] = $class;
	_Registered::$plural['Many' . $class] = $class;
	define('Model\\' . $name, $class);
	define('Model\Many' . $name, '_Many' . $class);
}

class Model {
	protected $_meta = array();
	public function __construct() {
		$args = func_get_args();
		
		if(count($args) > 1) {
			$data = array();
			while(!empty($args)) {
				$data[array_shift($args)] = array_shift($args);
			}
		} else if(is_array(current($args))) {
			$data = array_shift($args);
		} 
		
		foreach(get_object_vars($this) as $field => $type) if(!in_array($field, array('_meta'))) {
			$this->_meta[$field] = $type;

			if(strpos($type, '_Many') === 0) {
				$this->$field = new Collection(substr($type, 5));
			} else {
				if(in_array('Model\Primitive', class_parents($type))) {
					if(Settings::$usePrimitives) {
						$this->$field = isset($data[$field]) ? $data[$field] : null;
					} else { 
						$this->$field = new $type(isset($data[$field]) ? $data[$field] : null);
					}
				} else { 
					$this->$field = isset($data[$field]) ? $data[$field] : (new $type);
				}
			}
		}
	}
}