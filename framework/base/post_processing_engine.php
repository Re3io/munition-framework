<?php
namespace framework\base;
class PostProcessingEngine {
  private $_queue = null;
  function __construct() {
    $this->queue = [];
  }
  
  public function queue($fn) {
    $this->_queue[] = $fn;
  }
  
  public function process() {
    try {
      foreach($this->_queue as $item) {
        $item();
      }
    } catch(Exception $e) {
      return;
    }
  }
}