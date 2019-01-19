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


/*
<?php
function str_concat($prefix, $string) {
    $len = strlen($prefix);
    $substr = substr($string, 0, $len);
    if ($substr != $prefix) {
        return $prefix." ".$string;
    } else {
        return $string;
    }
}

echo str_concat("hello", "m9rco");
echo PHP_EOL;
echo str_concat("hello", "hello m9rco");
echo PHP_EOL;
*/

echo str_concat("hello", "m9rco");
echo PHP_EOL;
echo str_concat("hello", "hello m9rco");
echo PHP_EOL;




/*
<?php
function array_concat ($arr, $prefix) {
    foreach($arr as $key => $val) {
        if (isset($prefix[$key])
                && is_string($val)
                && is_string($prefix[$key])) {
            $arr[$key] = $prefix[$key].$val;
        }
    }
    return $arr;
}

$arr = array(
    0 => '0',
    1 => '123',
    'a' => 'abc',
);
$prefix = array(
    1 => '456',
    'a' => 'def',
);
var_dump(array_concat($arr, $prefix));
?>
*/

$arr = array(
    0 => '0',
    1 => '123',
    'a' => 'abc',
);
$prefix = array(
    1 => '456',
    'a' => 'def',
);
var_dump(array_concat($arr, $prefix));


/*
	<?php
    	define("__ARR__", array('2', 'site'=>"m9rco.cn"));
		define("__SITE__", "m9rco.cn", true);
		define("say\__SITE__", "m9rco.cn");
    	var_dump(__ARR__);
		var_dump(__site__);
		var_dump(say\__SITE__);
	?>

*/
    	var_dump(__ARR__);
		var_dump(__site__);
		var_dump(say\__SITE__);
        echo PHP_EOL;

/*
    $factory = new factory();
    var_dump($factory->product);
    $factory->production("love");
    var_dump($factory->product);
*/

    $factory = new factory();
    var_dump($factory->product);
    var_dump($factory->product);
