<?php

namespace html;

require_once 'tags.inc.php';

class Node { 
	private $name;	
	private $content = array();
	private $attributes = array(); 
	private $orphan; 
	public function __construct($name, $content, $orphan = false) {
		$this->name = $name;
		$this->content = array();
		$this->orphan = $orphan; 
		
		$content = flatten($content); 
		
		while(!empty($content)) {
			$item = array_shift($content);
			if(is_scalar($item)) {
				$this->attributes[$item] = array_shift($content);
			} else { 
				$this->content[] = $item;
			}
		}
	}

	public function renderAttributes() {
		$value = array();

		foreach($this->attributes as $key => $val) {
			$value[] = "$key=\"" . str_replace('"', '\"', is_array($val) ? implode(' ', $val) : $val) . "\"";
		}

		return empty($value) ? '' : (' ' . implode(' ', $value));
	}
	
	public function __toString() {

		return '<' . $this->name . $this->renderAttributes() 
			. ($this->orphan ? 
				' />' 
				:  
				(">\n" 
					. implode('', array_map(function($el) { return (string)$el; }, $this->content)) 
					. "\n</" . $this->name . ">\n"));
	}
}

class Text {
	private $value;  
	public function __construct($value) {
		$this->value = $value;
	}
	
	public function __toString() { 
		return $this->value;
	}
}

function text() { 
	return new Text(implode(' ', func_get_args()));
}

function texts() {
	return array_map(function($t) { return text($t); }, func_get_args()); 
}

function flatten ($array)  {
	$flat = array();
	foreach($array as $val){
		if(is_array($val)) { 
			$flat = array_merge($flat, flatten($val));
		} else { 
			$flat[] = $val;
		}
	}
	return $flat;
};

function tags($tag, $args) {
	$details = array();
	$content = array();
	foreach(flatten($args) as $arg) {
		if($arg instanceof Text || $arg instanceof Node) {
			$content[] = $arg;
		} else {
			$details[] = $arg;
		}
	}
	
	$result = array();
	foreach($content as $item) {
		$callWith = $details;
		$callWith[] = $item;
		$result[] = call_user_func_array("html\\$tag", $callWith);
	}
	return $result;
}

function output() {
	print(implode("\n", func_get_args()));
}

function orphanTag() {
	$args = func_get_args();
	return new Node(array_shift($args), $args, true);
}

class Includes { 
	public static $css = array();
	public static $js = array();
}

function requireCSS() {
	Includes::$css[] = func_get_args();
}

function requireJS() { 
	Includes::$js[] = func_get_args();
}

function getCSSTags($base) {
	return array_map(function($css) use($base) {
		return orphanTag(link, 
			href, $base . '/style/' . $css . '.css', 
			rel, 'stylesheet', 
			type, 'text/css');
	}, flatten(Includes::$css));
}

function getJSTags($base) {
	return array_map(function($js) use($base) {
		return script(
			type, 'text/javascript',
			src, $base . '/scripts/' . $js . '.js');
	}, flatten(Includes::$js));
}

function splitArgs($args) {
	$return = array(
		'attrs' => array(),
		'content' => array());
		
	while(!empty($args)) {
		$arg = array_shift($args);
		if(is_array($arg) || $arg instanceof Text || $arg instanceof Node) {
			$return['content'][] = $arg;
		} else {
			$return['attrs'][] = $arg;
			$return['attrs'][] = array_shift($args);
		}	
	}
	return $return;
}

function _genId() {
	static $id;
	
	if(!$id) { 
		$id = 0;
	}
	
	return 'GENID-' . $id++;
}

function _stack($class, $args) {
	$args = splitArgs($args);
	if(empty($args['attrs'])) {
		$args['attrs'][] = id;
		$args['attrs'][] = _genId();
	}
	return div($args['attrs'], 
				divs(KLASS, $class, $args['content']), 
				div(style, 'clear:both'));	
}

function vstack() {
	return _stack('vstack', func_get_args());
}

function hstack() {
	return _stack('hstack', func_get_args());
}