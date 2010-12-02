<?php

namespace html;

class Variable { 
	private static $vars = array();

	public static function setVar($name, $value) {
		static::$vars[$name] = $value;
		return $value;
	}

	public static function getVar($name) {
		if(!isset(static::$vars[$name])) { 
			 throw new \Exception("Var $name does not exist");
		}

		return static::$vars[$name];
	}
}

class Rule {
	protected $rules = array();

	public function __construct($args) {
		while(!empty($args)) {
			$this->rules[array_shift($args)] = array_shift($args);
		}
	}
	
	public function set($what, $with) {
		$this->rules[$what] = $with;
		return $this;
	}

	public function __call($what, $with) { 
		return $this->set($what, array_shift($with));
	}

	public function getRules() {
		return $this->rules;
	}

	public function render() {
		$rules = array();
		foreach($this->rules as $rule => $value) {
			if($rule == 'content') { 
				$value = "'$value'";
			}
			
			$rules[] = "\t{$rule}: {$value};";
		}
		return implode("\n", $rules);
	}
}

class MixIn extends Rule {
	static $mixins = array();
	
	private $name;
	
	public function __construct($args) {
		$this->name = array_shift($args);
		parent::__construct($args);
		self::$mixins[$this->name] =& $this;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public static function get($mixin) { 
		if(!static::exists($mixin)) {
			throw new \Exception("Mixin $mixin is not found");
		}
		return static::$mixins[$mixin];
	}
	
	public static function exists($mixin) {
		return isset(static::$mixins[$mixin]);
	}
}

class Selector extends Rule {
	protected $selector = false;
	protected $children = array();
	
	public function __construct($args) {
		$this->selector = array_shift($args);
		$filtered = array();
		foreach($args as $arg) {
			if($arg instanceOf Selector) {
				$this->children[] = $arg;
			} else {
				$filtered[] = $arg;
			}
		}
		$callWith = $filtered; 
		array_unshift($callWith, $this->selector);
		$mixin = call_user_func_array("\html\MixIn", $callWith);
		parent::__construct($filtered);
	}
	
	public function getSelector() {
		return $this->selector;
	}
	
	public function setSelector($selector) { 
		$this->selector = $selector;
		return $this;
	}
	
	public function mixIn($mixins) {
		foreach($mixins as $mixin) {
			$this->rules = array_merge($this->rules, MixIn::get($mixin)->getRules());
		}
		return $this;
	}

	private function mix() {
		if(isset($this->rules[MIXIN])) {
			$mixin = array_map('trim', explode(';', $this->rules[MIXIN]));
			unset($this->rules[MIXIN]);
			
			$this->mixIn($mixin);
			if(isset($this->rules[MIXIN])) {
				$this->mix();
			}
		}
		return $this;
	}
	
	private function renderChildren() {
		$selectors = explode(',', $this->selector);
		return implode("\n", array_map(function($child) use($selectors) {
			return $child->setSelector(
				implode(', ', array_map(
					function($s) use($selectors) {
						return implode(', ', array_map(
							function($sel) use($s) {
								return trim($sel) . ' ' . trim($s);
							}, $selectors));
					}, explode(',', $child->getSelector()))))->render();
		}, $this->children));
	}

	public function render() {
		$this->mix();
		$rendered = parent::render();
		return (empty($rendered) ? '' : "$this->selector {\n" . $rendered . "\n}\n") . $this->renderChildren();
	}

	public function __toString() {
		return $this->render();
	}
}

function MixIn() {
	$mixin = new MixIn(func_get_args());
	return $mixin->getName();
}

function Size($what, $value = false) {
	return $value !== false ? Variable::setVar('Size-' . $what, $value) : Variable::getVar('Size-' . $what);
}

function Color($what, $value = false) {
	return $value !== false ? Variable::setVar('Color-' . $what, $value) : Variable::getVar('Color-' . $what);
}

function Selector() {
	return new Selector(func_get_args());
}

function S() {
	return new Selector(func_get_args());
}

function RoundCorners($size) {
	$mixin = '__RoundCorners-' . $size;
	if(!MixIn::exists($mixin)) {
		MixIn($mixin,
			_WEBKIT_BORDER_RADIUS, "{$size}px",
			_MOZ_BORDER_RADIUS, "{$size}px",
			BORDER_RADIUS, "{$size}px");
	}
	return $mixin;
}