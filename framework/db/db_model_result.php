<?php
namespace framework\db;
class DbModelResult {

  private $_attrs = null;
  private $__className;
  
  function __construct($attrs, $to) {
    $this->_attrs = $attrs;
    $this->__className = $to;
  }
  
  
  public function __get($attr) {
    if(isset($this->_attrs[$attr])) {
      return $attr;
    } else {
      return null;
    }
  }
  
  public function instance() {
    if($this->_attrs == null) {
      return null;
    } else {
      return call_user_func(array($this->__className, "make"), $this->_attrs);
    }
  }
  public function toString() {
    return print_r($this->_attrs, false);
  }
}