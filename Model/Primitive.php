<?php
namespace Model;

class Primitive { 
	public $value; 
	public function __construct($value = null) {
		$this->value = $value;
	}
	
	public function __toString() {
		return (string)$this->value;
	}
	
	public function __get($func) {
		if(is_callable(array($this, $func))) { 
			return $this->$func();
		}
	}
}