<?php
namespace Munition;
class Router {

  private $_routes = null;
  private $_patterns = null;
  private $_prepend = false;
  
  
  protected $base = "";
  protected $initial_scope = [];
  
  public $app = null;
  
  function __construct() {
    
    $this->_routes = [];
    $this->_patterns = [];
  }
  
  public function pattern($name, $pattern) {
    $name = str_replace(":", "", $name);
    $this->_patterns[":".$name] = "(?P<".$name.">".$pattern.")";
  }
  
  public function prepend($cb) {
    $this->_prepend = true;
    $cb($this);
    $this->_prepend = false;
  }
  public function append($cb) {
    $this->_prepend = false;
    $cb($this);
  }
  public function map($cb) {
    $this->_routes = [];
    $this->_prepend = false;
    $cb($this);
  }
  
  public function request($path, $type, $controller, $params = []) {
    $r = [
      "path" => $path,
      "method" => $type,
      "controller" => $controller,
      "params" => 0
    ];
    $regex = preg_quote($path, "/");
    foreach($params as $k=>$p) {
      $params[":".$k] = "(?P<".$k.">".$p.")";
    }
    foreach(array_merge($this->_patterns, $params) as $name => $pat) {
      if(strpos($path, $name) !== false) {
        $regex = str_replace("\\".$name, $pat, $regex);
        $r["params"]++;
      }
    }
    if(substr($regex, -1) === "/") {
      $regex .= "?";
    } else {
      $regex .= "\\/?";
    }
    $regex = "/^" . $regex . "(?P<_request_format>\.[a-zA-Z0-9]{1,4})?$/";
    $r["regex"] = $regex;
    preg_match($r["regex"], "");
    
    if($this->_prepend == true)
      array_unshift($this->_routes, $r);
    else
      $this->_routes[] = $r;
  }
  
  public function get($path, $controller, $params = []) {
    $this->request($path, "GET", $controller, $params);
  }
  
  public function head($path, $controller, $params = []) {
    $this->request($path, "HEAD", $controller, $params);
  }
  
  public function post($path, $controller, $params = []) {
    $this->request($path, "POST", $controller, $params);
  }
  
  public function put($path, $controller, $params = []) {
    $this->request($path, "PUT", $controller, $params);
  }
  
  public function delete($path, $controller, $params = []) {
    $this->request($path, "DELETE", $controller, $params);
  }
  
  public function resource($path, $model, $params = [ "constraint" => ResourceConstraint::$default ]) {
    
  }
  
  public function route($request, $method = "GET") {
    $p = [];
    $params = [];
    $f = false;
    $path = str_replace($this->base, "", $request);
    foreach($this->_routes as $route) {
      if($route["method"] == $method && preg_match($route["regex"], $path, $params) === 1) {
        unset($params[0]);
        $this->call_controller_function($route["controller"], $params);
        $f = true;
        break;
      }
    }
    if($f === false) {
      if(isset($this->_routes["404"])) {
        $this->call_controller_function($this->_routes["404"]["controller"], ["request"=>$request, "path" => $path]);
      } else { 
        throw new \Exception("No matching route found in Router: (".$request . ":".$path.")\n" . print_r($this->_routes, true));
      }
    }
  }
  
  public function error($errCode, $controller) {
    $this->_routes[$errCode] = ["method"=>"ANY", "regex"=>"/^$/","controller" => $controller, "params" => 0];
  }
  
  private function call_controller_function($ctrlfn, $params) {
    $format = isset($params["_request_format"]) ? substr($params["_request_format"], 1) : "html";
    if(is_callable($ctrlfn)) {
      $ctrlfn($this->initial_scope, $params, $format, $this->app);
    } else {
      AppController::call_function($ctrlfn, $this->initial_scope, $params, $format, $this->app);
    }

  }
}