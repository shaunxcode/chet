<?php
namespace Model;

class TypeString extends Primitive {
	public function reverse() {
		return new TypeString(strrev($this->value));
	}
	
	public function upper() {
		return new TypeString(strtoupper($this->value));
	}
	
	public function md5() {
		return new TypeString(md5($this->value));
	}
}
register('String', '\Model\TypeString');