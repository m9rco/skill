<?php

 echo PHP_EOL;
/*

 */
echo say();


echo PHP_EOL;
/*
    function default_value ($type, $value = null) {
        if ($type == "int") {
            return $value ?? 0;
        } else if ($type == "bool") {
            return $value ?? false;
        } else if ($type == "str") {
            return is_null($value) ? "" : $value;
        }
        return null;
    }

*/
var_dump(default_value("int"));
var_dump(default_value("int", 1));


echo PHP_EOL;
/*
   function get_size ($value) {
        if (is_string($value)) {
            return "string size is ". strlen($value);
        } else if (is_array($value)) {
            return "array size is ". sizeof($value);
        } else {
              return "can not support";
        }
    }
*/
var_dump(get_size("abc"));
var_dump(get_size(array(1,2)));


/*
    $lng = 2;
    $str = "abc";
    $arr = array(1,'a' => 'b');

    var_dump($str);
    var_dump($arr);
    var_dump($obj);
*/
class demo {}
define_var();

var_dump($str);
var_dump($arr);