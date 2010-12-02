<?php

namespace Chet;

class Application {
	public static $libraries = array(); 
	
	public static $params = array();

	public static $container = false;

	public static $base = '/';

	public static $view = false;
	
	public static $routes = array(
		'get' => array(),
		'post' => array());

	public static function route($uri, $method) {
		if(strpos($uri, self::$base) == 0) {
			$uri = substr($uri, strlen(self::$base));
		}

		$executeAction = false;
		$uriParts = explode('/', $uri); 
		foreach(self::$routes[$method] as $route => $action) {
			$routeParts = explode('/', $route);
			if(count($uriParts) == count($routeParts)) {
				$params = array();
				$view = '';
				$found = false;
				foreach($routeParts as $place => $part) {
					if(!empty($part) && $part[0] == '$') { 
						$params[substr($part, 1)] = $uriParts[$place];
					} else if($uriParts[$place] == $part) { 
						$found = true;
						$view .= $part;
					} else {
						$found = false;
						break;
					}
				}
				if($found) {
					$executeAction = $action;
					self::$params = $params;
					$viewFile = AppRoot() . '/View/' . (empty($view) ? 'index' : $view) . '.php';
					break;
				}
			} 
		}

		//allow implicit default route i.e. /about to 
		if(!$executeAction) { 

			$viewFile = AppRoot(). '/View/' . $uri . '.php';
			if(file_exists($viewFile)) {
				$executeAction = function(){};
			}
		}

		if($executeAction) {
			//assume a view, css 
			$result = call_user_func_array($executeAction, self::$params);

			if(is_array($result)) {
				extract($result);
			}

			if(View()) { 
				$viewFile = AppRoot() . '/View/' . View() . '.php';
			}
				
			if(!file_exists($viewFile)) {
				die("Can not find $viewFile");
			} else {
				require_once 'html.php';
				require_once 'css.php';
								
				ob_start();
				include $viewFile; 
				$content = ob_get_contents();
				ob_end_clean();

				if(self::$container) {
					include '../view/' . self::$container . '.php';					
				} else { 
					echo $content;
				}
			}
		} else {
			die("Could not find route for $uri");
		}
	}
}

function Param($name, $default = false) {
	return isset(Application::$params[$name]) ? Application::$params[$name] : $default;
}

function Base($value = false) {
	if($value) {
		Application::$base = $value;
	}
	return Application::$base;
}

function Container($name) {
	Application::$container = $name;
}

function View($value = false) {
	if($value) { 
		Application::$view = $value;
	}
	return Application::$view;
}

function Get($pattern, $action) {
	Application::$routes['get'][$pattern] = $action;
}

function Post($pattern, $action) {
	Application::$routes['post'][$pattern] = $action;
}

function Put($pattern, $action) { 
	Application::$routes['put'][$pattern] = $action;
}

function Delete($pattern, $action) {
	Application::$routes['delete'][$pattern] = $action;
}

function Dispatch() {
	Application::route($_SERVER['REQUEST_URI'], strtolower($_SERVER['REQUEST_METHOD'])); 
}

function UseLibrary() { 
	Application::$libraries = array_merge(Application::$libraries, func_get_args());
}

function AppRoot($set = false) {
	static $appRoot;
	if($set) {
		$appRoot = $set;
	}
	
	if(!$appRoot) { 
		$appRoot = dirname(__FILE__);
	}

	return $appRoot;
}

spl_autoload_register(function($name) {
	$parts = explode('\\', $name);
	if(current($parts) == 'Chet') { 
		array_shift($parts);
	}
	
	$type = array_shift($parts);
	if(in_array($type, Application::$libraries)) {
		array_unshift($parts, 'Library', $type);		
	} else {
		array_unshift($parts, $type); 
	}
	
	$fileBase = AppRoot() . '/' . implode('/', $parts);
	if(!file_exists($fileBase . '.php') && file_exists($file = $fileBase . '/' . end($parts) . '.php')) {
		$file = $file;
	} else { 
		$file = $fileBase .= '.php';
	}
	
	require_once $file;
});