<?php
// ------------- Generic functions use in Munition ---------------------
// TODO: probably prefix these
function filename_to_classname($file) {
  $file = strtolower($file);
  if(strpos($file, ".") !== false) {
    $file = pathinfo($file, PATHINFO_FILENAME);
  }
  if(strpos($file, "_") !== false) {
    $p = explode("_", $file);
    foreach($p as $i=>$part) {
      $p[$i] = ucfirst($part);
    }
    return implode("", $p);
  } else {
    return ucfirst($file);
  }
}

function classname_to_filename($class) {
  $filename = "";
  $last = "";
  foreach(str_split($class) as $c) {
    if(ctype_upper($c)) {
      if($filename == "" || $last == "/") {
        $filename .= strtolower($c);
      } else {
        $filename .= "_" . strtolower($c);
      }
    } else {
      $filename .= $c;
    }
    $last = $c;
  }
  return $filename;
}

function pluralize($name) {
  if(substr($name, strlen($name)-1) == "s")
    return $name;
  else
    return $name . "s";
}

function singularize($name) {
  if(substr($name, strlen($name)-1) == "s")
    return substr($name, 0, strlen($name)-1);
  else
    return $name;
}


// ------------- Autoload Munition Libraries ---------------------
spl_autoload_register(function($class){
    $class = classname_to_filename(str_replace('\\', '/', $class));
    if(file_exists('./framework/lib/' . $class . '.php')) {
      require_once('./framework/lib/' . $class . '.php');
    }
});



// ------------- Define Environment ---------------------
$env = getenv("MUNITION_ENV");
define("MUNITION_ENV", in_array($env, ["production", "development", "test"])? $env : "development");
define("MUNITION_ROOT", dirname($_SERVER['SCRIPT_FILENAME']));

set_include_path(get_include_path() . PATH_SEPARATOR . MUNITION_ROOT . "/framework/lib");

if(MUNITION_ENV == "test" && !defined('SIMULATES_WEBSERVER')) return; // web_constants and error handling only in non-testing env
require 'web_constants.php';


// ------------- Error Handling ---------------------
require 'munition_exception.php';
set_error_handler(function($errno, $errstr, $errfile = null, $errline = 0, $errcontext = null) {
  throw new MunitionException($errno, $errstr, $errfile, $errline, $errcontext);
}, E_ALL);

set_exception_handler(function($e) {
  $err = "Uncaught exception '" . get_class($e) . "'";
  $errmsg = $e->getMessage();
  
  $l = $e->getLine();
  $errtrace = "Exception in '". $e->getFile() . "'\n\nLine $l:\n";
  $lines = file($e->getFile());
  
  $l = $l-3 >= 0 ? $l-3 : 0;
  $lines = array_splice($lines, $l, 5);
  
  
  foreach($lines as $i=>$line) {
    if($i == 2) {
      $lines[$i] = "<span style='color:red;'>>>> ".htmlentities($line)."</span>";
    } else {
      $lines[$i] = "    ".htmlentities($line);
    }
  }
  $errtrace .= implode("", $lines);
  
  $errtrace .= "\r\n\r\nStack Trace: \n\n" . $e->getTraceAsString();
  require 'errhandler/page.php';
  die();
});

// ------------- Code only used for testing rewrite rules ---------------------
// TODO: move it out of this file
if(str_replace(array("/","\\"), "", __FILE__) === str_replace(array("/","\\"), "", $_SERVER["SCRIPT_FILENAME"])) {
  http_response_code(422);
}