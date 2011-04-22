<?php
namespace Model;

class Collection {
	public $class;
	public function __construct($class) {
		$this->class = $class;
	}
}