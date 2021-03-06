<?php
if(!class_exists('TestApplication')):
class TestApplication extends \Munition\App {
  function setup() {
    $this->configure("./tests/test_app/");

    $this->db = new \DbModel\AppDbManager();
    
    $r = $this->router;
    require 'routes.php';
    
    $config = $this;
    require 'env/' . MUNITION_ENV . '.php';
  }
}
endif;
return new TestApplication();
