# php 扩展开发笔记

从0至1开发PHP扩展学习笔记

  * [传参与返回值](#传参与返回值)
     * [C 代码实现](#c-代码实现)
     * [代码说明](#代码说明)
        * [获取参数](#获取参数)
              * [zend_parse_parameters](#zend_parse_parameters)
              * [FAST_ZPP](#fast_zpp)
              * [返回值](#返回值)
  * [类型处理](#类型处理)
     * [C 代码实现](#c-代码实现-1)
     * [类型相关宏方法](#类型相关宏方法)
     * [数组](#数组)
     * [字符串拼接](#字符串拼接)
  * [创建变量](#创建变量)
     * [C 代码实现](#c-代码实现-2)
     * [创建变量](#创建变量-1)
     * [设置本地变量](#设置本地变量)

## 传参与返回值

### PHP 代码实现

```php
<?php
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
 
    var_dump(default_value("int"));
    var_dump(default_value("int", 1));
    var_dump(default_value("bool"));
    var_dump(default_value("bool", true));
    var_dump(default_value("str"));
    var_dump(default_value("str", "a"));
    var_dump(default_value("array"));
?>
```

### C 代码实现

```C
PHP_FUNCTION(default_value)
{
    zend_string     *type;    
    zval            *value = NULL;
 
#ifndef FAST_ZPP
    /* Get function parameters and do error-checking. */
    if (zend_parse_parameters(ZEND_NUM_ARGS(), "S|z", &type, &value) == FAILURE) {
        return;
    }    
#else
    ZEND_PARSE_PARAMETERS_START(1, 2)
        Z_PARAM_STR(type)
        Z_PARAM_OPTIONAL
        Z_PARAM_ZVAL_EX(value, 0, 1)
    ZEND_PARSE_PARAMETERS_END();
#endif
     
    if (ZSTR_LEN(type) == 3 && strncmp(ZSTR_VAL(type), "int", 3) == 0 && value == NULL) {
        RETURN_LONG(0);
    } else if (ZSTR_LEN(type) == 3 && strncmp(ZSTR_VAL(type), "int", 3) == 0 && value != NULL) {
        RETURN_ZVAL(value, 0, 1); 
    } else if (ZSTR_LEN(type) == 4 && strncmp(ZSTR_VAL(type), "bool", 4) == 0 && value == NULL) {
        RETURN_FALSE;
    } else if (ZSTR_LEN(type) == 4 && strncmp(ZSTR_VAL(type), "bool", 4) == 0 && value != NULL) {
        RETURN_ZVAL(value, 0, 1); 
    } else if (ZSTR_LEN(type) == 3 && strncmp(ZSTR_VAL(type), "str", 3) == 0 && value == NULL) {
        RETURN_EMPTY_STRING();
    } else if (ZSTR_LEN(type) == 3 && strncmp(ZSTR_VAL(type), "str", 3) == 0 && value != NULL) {
        RETURN_ZVAL(value, 0, 1); 
    } 
    RETURN_NULL();
}
```

### 代码说明

#### 获取参数

在PHP7中提供了两种获取参数的方法。`zend_parse_parameters`和`FAST_ZPP`方式。

###### zend_parse_parameters

在PHP7之前一直使用`zend_parse_parameters`函数获取参数。这个函数的作用，就是把传入的参数转换为PHP内核中相应的类型，方便在PHP扩展中使用。

参数说明：
- 第一个参数，参数个数。一般就使用`ZEND_NUM_ARGS()`，不需要改变。
- 第二个参数，格式化字符串。这个格式化字符串的作用就是，指定传入参数与PHP内核类型的转换关系。

代码中 `S|z` 的含义就是：
- S 表示参数是一个字符串。要把传入的参数转换为zend_string类型。
- | 表示之后的参数是可选。可以传，也可以不传。
- z 表示参数是多种类型。要把传入的参数转换为zval类型。

除此之外，还有一些specifier，需要注意：
- ！如果接收了一个PHP语言里的null变量，则直接把其转成C语言里的NULL，而不是封装成IS_NULL类型的zval。
- / 如果传递过来的变量与别的变量共用一个zval，而且不是引用，则进行强制分离，新的zval的is_ref__gc==0, and refcount__gc==1.

更多格式化字符串的含义可以查看官方网站。https://wiki.php.net/rfc/fast_zpp

###### FAST_ZPP

在PHP7中新提供的方式。是为了提高参数解析的性能。对应经常使用的方法，建议使用FAST_ZPP方式。

使用方式：

以 `ZEND_PARSE_PARAMETERS_START(1, 2)` 开头。

第一个参数表示必传的参数个数，第二个参数表示最多传入的参数个数。
以`ZEND_PARSE_PARAMETERS_END();`结束。
中间是传入参数的解析。
值得注意的是，一般`FAST_ZPP`的宏方法与`zend_parse_parameters`的`specifier`是一一对应的。如：
- Z_PARAM_OPTIONAL 对应 |
- Z_PARAM_STR	   对应 S

但是，`Z_PARAM_ZVAL_EX`方法比较特殊。它对应两个`specifier`，分别是 ! 和 / 。! 对应宏方法的第二个参数。/ 对应宏方法的第三个参数。如果想开启，只要设置为1即可。
FAST_ZPP 相应的宏方法可以查看官方网站 https://wiki.php.net/rfc/fast_zpp#proposal

###### 返回值

方法的返回值是使用`RETURN_`开头的宏方法进行返回的。常用的宏方法有：

- RETURN_NULL()	返回null
- RETURN_LONG(l)	返回整型
- RETURN_DOUBLE(d) 返回浮点型
- RETURN_STR(s)	返回一个字符串。参数是一个zend_string * 指针
- RETURN_STRING(s)	返回一个字符串。参数是一个char * 指针
- RETURN_STRINGL(s, l) 返回一个字符串。第二个参数是字符串长度。
- RETURN_EMPTY_STRING()	返回一个空字符串。
- RETURN_ARR(r)	返回一个数组。参数是zend_array *指针。
- RETURN_OBJ(r) 返回一个对象。参数是zend_object *指针。
- RETURN_ZVAL(zv, copy, dtor) 返回任意类型。参数是 zval *指针。
- RETURN_FALSE	返回false
- RETURN_TRUE	返回true

## 类型处理

### PHP 代码实现

分别获取string 和 array的长度


```php
<?php
   function get_size ($value) {
        if (is_string($value)) {
            return "string size is ". strlen($value);
        } else if (is_array($value)) {
            return "array size is ". sizeof($value);
        } else {
              return "can not support";
        }
    }

    var_dump(get_size("abc"));
    var_dump(get_size(array(1,2)));
?>
```

### C 代码实现

`zval`变量相关的宏方法大部分定义在`Zend/zend_types.h`文件中。


```C
PHP_FUNCTION(get_size)
{
    zval *val;
    size_t size;
    zend_string *result;
    HashTable *myht;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "z", &val) == FAILURE) {
        return;
    }

    if (Z_TYPE_P(val) == IS_STRING) {
        result = strpprintf(0, "string size is %d", Z_STRLEN_P(val));
    } else if (Z_TYPE_P(val) == IS_ARRAY) {
        myht = Z_ARRVAL_P(val);
        result = strpprintf(0, "array size is %d", zend_array_count(myht));
    } else {
        result = strpprintf(0, "can not support");
    }

    RETURN_STR(result);
}
```

### 类型相关宏方法

Z_TYPE_P(zval *) 获取zval变量的类型。常见的类型都有：

```c
#define IS_UNDEF                    0
#define IS_NULL                     1
#define IS_FALSE                    2
#define IS_TRUE                     3
#define IS_LONG                     4
#define IS_DOUBLE                   5
#define IS_STRING                   6
#define IS_ARRAY                    7
#define IS_OBJECT                   8
#define IS_RESOURCE                 9
#define IS_REFERENCE                10
```

`Z_STRLEN_P(zval *)`获取字符串的长度。

###  数组
在 `Zend/zend_hash.c`文件中包含一些array处理的方法。

`zend_array_count(HashTable *)` 获取数组的元素个数。
`zend_array` 和 `HashTable`其实是相同的数据结构。在`Zend/zend_types.h`文件中有定义。

```c
typedef struct _zend_array HashTable;
```

### 字符串拼接
`strpprintf`是PHP为我们提供的字符串拼接的方法。第一个参数是最大字符数。


## 创建变量

### PHP 代码实现


```php
<?php
class demo {}

$lng = 2;
$str = "abc";
$arr = array(1,'a' => 'b');
$obj = new demo();

var_dump($str);
var_dump($arr);
var_dump($obj);
?>
```

### C 代码实现

注意，下面的内容，我们把PHP扩展中的zval结构成为变量，把PHP代码中的变量成为本地变量。
创建本地变量主要分两步，创建变量和设置为本地变量。

```c
PHP_FUNCTION(define_var)
{
    zval var_value; //变量的值
    zend_string *var_name = NULL; //变量名称

      //创建整型变量
    ZVAL_LONG(&var_value, 2);
    zend_set_local_var_str("lng", 3 , &var_value, 0); //设置本地变量
    ZVAL_NULL(&var_value);

    //创建字符串变量
    zend_string *str = NULL;
    char content[4] = "abc";
    var_name = zend_string_init("str", 3, 0); //设置变量名称
    str = zend_string_init(content, sizeof(content) - 1, 0);
    ZVAL_STR(&var_value, str); //设置变量的值
    zend_set_local_var(var_name, &var_value, 0); //设置本地变量
    zend_string_release(var_name);
    ZVAL_NULL(&var_value);

    //创建数组变量
    var_name = zend_string_init("arr", 3, 0); //设置变量名称
    array_init(&var_value);
    add_index_long(&var_value, 0, 1);
    add_assoc_stringl_ex(&var_value, "a", 1, "b", 1);
    zend_set_local_var(var_name, &var_value, 0); //设置本地变量
    zend_string_release(var_name);
    ZVAL_NULL(&var_value);

    //创建对象变量
    zend_class_entry *ce;
    zend_string *class_name;
    class_name = zend_string_init("demo", 4, 0);
    ce = zend_fetch_class(class_name, ZEND_FETCH_CLASS_AUTO); //获取类
    zend_string_release(class_name);
    object_init_ex(&var_value, ce);
    zend_set_local_var_str("obj", 3, &var_value, 0); //设置本地变量
    ZVAL_NULL(&var_value);
}
```


### 创建变量

变量的类型有多种，在创建变量的方式也有所不同。
对于简单的数据类型，创建变量很简单。只需调用相应的宏方法就可以。
这些方法在Zend/zend_types.h文件中，宏方法以ZVAL_开头。如：

键|说明
---|---
ZVAL_NULL|	设置为null
ZVAL_FALSE|	设置为false。
ZVAL_TRUE|	设置为true
ZVAL_BOOL|	设置bool。
ZVAL_LONG|	设置long。
ZVAL_DOUBLE|设置为double。

使用方法，可以参考上面代码中ZVAL_LONG的调用。
对于数组，对象，字符串等复杂数据类型。比较麻烦。可以参考上面的示例代码。

### 设置本地变量

设置本地变量Zend引擎为我们提供了两个方法。两个函数的使用，都在以上的代码中做了演示。这两个方法的应用场景有所差别。

** zend_set_local_var **
如果已经存在类型为zend_string的变量名，则使用这个方法创建本地变量

** zend_set_local_var_str **
如果没有类型为zend_string的变量名，使用此方法创建本地变量

## 字符串处理

### PHP 代码实现

```
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
?>
```

### C 代码实现

```C
PHP_FUNCTION(str_concat)
{
    zend_string *prefix, *subject, *result;
    zval *string;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "Sz", &prefix, &string) == FAILURE) {
       return;
    }

    subject = zval_get_string(string);
    if (zend_binary_strncmp(ZSTR_VAL(prefix), ZSTR_LEN(prefix), ZSTR_VAL(subject), ZSTR_LEN(subject), ZSTR_LEN(prefix)) == 0) {
        RETURN_STR(subject);
    }
    result = strpprintf(0, "%s %s", ZSTR_VAL(prefix), ZSTR_VAL(subject));
    RETURN_STR(result);
}
```

### 结构分析

zend_string是PHP7新增的结构。结构如下：

```
struct _zend_string {
    zend_refcounted_h gc; /*gc信息*/
    zend_ulong        h;  /* hash value */
    size_t            len; /*字符串长度*/
    char              val[1]; /*字符串起始地址*/
};
```

[Zend/zend_string.h](https://github.com/php/php-src/blob/PHP-7.0.19/Zend/zend_string.h#L21)提供了一些zend_string处理的一些方法。
`ZSTR_`开头的宏方法是zend_string结构专属的方法。主要有如下几个：

https://github.com/php/php-src/blob/42b8d368f83c6484f8ae8c80a9bb56cf4f46d3e2/Zend/zend_string.h#L40

```
#define ZSTR_VAL(zstr)  (zstr)->val
#define ZSTR_LEN(zstr)  (zstr)->len
#define ZSTR_H(zstr)    (zstr)->h
#define ZSTR_HASH(zstr) zend_string_hash_val(zstr)
```

`ZSTR_VAL` `ZSTR_LEN ZSTR_H`宏方法分别对应`zend_string`结构的成员。`ZSTR_HASH`是获取字符串的hash值，如果不存在，就调用hash函数生成一个。

代码中故意把第二个参数转换成zval。主要是为了展现zend为我们提供了一系列的操作方法。如，`zval_get_string`,`zend_binary_strncmp`。
这些方法在`Zend/zend_operators.h`文件中。

更多宏方法请查看 Zend/zend_API.h中的相关代码。

## 数组处理

### PHP 代码实现

把两个数组，相同key的字符串值拼接。

```
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
```

### C 代码实现

```
PHP_FUNCTION(array_concat)
{
    zval *arr, *prefix, *entry, *prefix_entry, value;
    zend_string *string_key, *result;
    zend_ulong num_key;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "aa", &arr, &prefix) == FAILURE) {
        return;
    }

    array_init_size(return_value, zend_hash_num_elements(Z_ARRVAL_P(arr)));

    ZEND_HASH_FOREACH_KEY_VAL(Z_ARRVAL_P(arr), num_key, string_key, entry) {
        if (string_key && zend_hash_exists(Z_ARRVAL_P(prefix), string_key)) {
            prefix_entry = zend_hash_find(Z_ARRVAL_P(prefix), string_key);
            if (Z_TYPE_P(entry) == IS_STRING && prefix_entry != NULL && Z_TYPE_P(prefix_entry) == IS_STRING) {
                result = strpprintf(0, "%s%s", Z_STRVAL_P(prefix_entry), Z_STRVAL_P(entry));
                ZVAL_STR(&value, result);
                zend_hash_update(Z_ARRVAL_P(return_value), string_key, &value);
            }
        } else if (string_key == NULL && zend_hash_index_exists(Z_ARRVAL_P(prefix), num_key)){
            prefix_entry = zend_hash_index_find(Z_ARRVAL_P(prefix), num_key);
            if (Z_TYPE_P(entry) == IS_STRING && prefix_entry != NULL && Z_TYPE_P(prefix_entry) == IS_STRING) {
                result = strpprintf(0, "%s%s", Z_STRVAL_P(prefix_entry), Z_STRVAL_P(entry));
                ZVAL_STR(&value, result);
                zend_hash_index_update(Z_ARRVAL_P(return_value), num_key, &value);
            }
        } else if (string_key) {
            zend_hash_update(Z_ARRVAL_P(return_value), string_key, entry);
            zval_add_ref(entry);
        } else  {
            zend_hash_index_update(Z_ARRVAL_P(return_value), num_key, entry);
            zval_add_ref(entry);
        }
    }ZEND_HASH_FOREACH_END();
```

### 结构分析

PHP中的数组本质上就是一个哈希。
对于哈希处理的方法主要集中在[Zend/zend_hash.h](https://github.com/php/php-src/blob/PHP-7.0.19/Zend/zend_hash.h)中。
对于数组的操作方法主要集中在[Zend/zend_API.h](https://github.com/php/php-src/blob/PHP-7.0.19/Zend/zend_API.h)。数组的方法其实就是对哈希处理方法的一层包装。
数组操作的方法主要是以`add_assoc_*` 和 `add_index_*`开头的一些列方法。

下面是代码中涉及的一些方法。
`zend_hash_num_elements`获取数组的元素个数。
`array_init_size(return_value, zend_hash_num_elements(Z_ARRVAL_P(arr)));` 初始化一个数组。
在PHP扩展中，我们是通过return_value这个变量设置方法的返回值。因此，我们直接修改这个`return_value`变量即可。感兴趣的话，可以把宏方法`PHP_FUNCTION`展开看下。
PHP7提供了一套宏方法用于遍历哈希和对哈希进行操作。这些宏方法主要放在Zend/zend_hash.h](https://github.com/php/php-src/blob/PHP-7.0.19/Zend/zend_hash.h)文件中。
如，代码中的`ZEND_HASH_FOREACH_KEY_VAL`就是一个变量哈希的宏。是不是和PHP代码中的foreach有点像？

在这里我们把代码中用到的哈希相关的方法做下整理说明：

关键词|用法
---|---
ZEND_HASH_FOREACH_KEY_VAL 和 ZEND_HASH_FOREACH_END 配合使用 |实现foreach的效果。
zend_hash_exists| 检测指定的key在哈希中是否存在。key为字符串。
zend_hash_index_exists| 检测指定的key在哈希中是否存在。key为数字。
zend_hash_find|	根据key查找指定的值。key为字符串。
zend_hash_index_find| 根据key查找指定的值。key为数字。
zend_hash_update |更新指定key的值。key为字符串。
zend_hash_index_update| 更新指定key的值。key为数字。

基本上有这些方法，你就可以对数组进行一些基本操作了。方法命名也很有规律，`key`为字符串和数字提供了两套。

`zval_add_ref(entry); `给数组的值，增加一次引用计数
`zend_hash_update`方法只自动给string_key自动增加了一次引用计数。
数组`return_value`共用数组arr的值。
因此，我们需要手动增加一次引用计数。


## 常量定义

### PHP 代码实现

```php
	<?php
    	define("__ARR__", array('2', 'site'=>"m9rco.cn"));
		define("__SITE__", "m9rco.cn", true);
		define("say\__SITE__", "m9rco.cn");
    	var_dump(__ARR__);
		var_dump(__site__);
		var_dump(say\__SITE__);
	?>
```

## C 代码实现

```C
//增加两个方法
//释放hash
static void say_hash_destroy(HashTable *ht)
    zend_string *key;
    zval *element;
    if (((ht)->u.flags & HASH_FLAG_INITIALIZED)) {
        ZEND_HASH_FOREACH_STR_KEY_VAL(ht, key, element) {
            if (key) {
                free(key);
            }
            switch (Z_TYPE_P(element)) {
                case IS_STRING:
                    free(Z_PTR_P(element));
                    break;
                case IS_ARRAY:
                    say_hash_destroy(Z_ARRVAL_P(element));
                    break;
            }
        } ZEND_HASH_FOREACH_END();
        free(HT_GET_DATA_ADDR(ht));
    }
    free(ht);
}
//释放数组和字符串
static void say_entry_dtor_persistent(zval *zvalue)
    if (Z_TYPE_P(zvalue) == IS_ARRAY) {
        say_hash_destroy(Z_ARRVAL_P(zvalue));
    } else if (Z_TYPE_P(zvalue) == IS_STRING) {
        zend_string_release(Z_STR_P(zvalue));
    }
}
//PHP_MINIT_FUNCTION(demo) 方法的PHP扩展源码： 扩展初始化的调用此方法
PHP_MINIT_FUNCTION(demo)
        {
                zend_constant c;
                zend_string *key;
                zval value;
                ZVAL_NEW_PERSISTENT_ARR(&c.value);
                zend_hash_init(Z_ARRVAL(c.value), 0, NULL,
        (dtor_func_t)say_entry_dtor_persistent, 1);
                add_index_long(&c.value, 0, 2);
                key = zend_string_init("site", 4, 1);
                ZVAL_STR(&value, zend_string_init("m9rco.cn", 12, 1));
                zend_hash_update(Z_ARRVAL(c.value), key, &value);
                c.flags = CONST_CS|CONST_PERSISTENT;
                c.name = zend_string_init("__ARR__", 7, 1);
                c.module_number = module_number;
                zend_register_constant(&c);

                REGISTER_STRINGL_CONSTANT("__SITE__", "m9rco.cn", 12, CONST_PERSISTENT);
                REGISTER_NS_STRINGL_CONSTANT("say", "__SITE__", "m9rco.cn", 8, CONST_CS|CONST_PERSISTENT);

                return SUCCESS;
        }
//扩展卸载的时候调用此方法
PHP_MSHUTDOWN_FUNCTION(demo)
        {
                zval *val;
                val = zend_get_constant_str("__ARR__", 7);
                say_hash_destroy(Z_ARRVAL_P(val));
                ZVAL_NULL(val);

                /* uncomment this line if you have INI entries
                UNREGISTER_INI_ENTRIES();
                */
                return SUCCESS;
        }
```

## 结构分析

一般情况下，在扩展中只建议定义`null`，`bool`，`long`，`double`，`string`几种类型的常量。因为内核只提供了这几种类型的宏方法。
常量定义的宏方法在[Zend/zend_constants.h](https://github.com/php/php-src/blob/PHP-7.0.19/Zend/zend_constants.h)文件中。想定义一个常量，很简单，只要调用对应的宏方法即可。如：
```
 REGISTER_STRINGL_CONSTANT("__SITE__", "m9rco.cn", 12, CONST_PERSISTENT);
```
宏方法的最后一个参数是一些标识符。

常量|说明
---|---
CONST_PERSISTENT| 表示为持久的。常驻内存。
CONST_CS	| 表示为区分大小写。

注意我们上面定义常量时使用的是__SITE__，但是调用的时候使用的是__site__。

还有一套可以指定命名空间的宏方法。宏方法中带NS。如：

```
REGISTER_NS_STRINGL_CONSTANT("say", "__SITE__", "m9rco.cn", 8, CONST_CS|CONST_PERSISTENT);
```

第一个参数就是命名空间。

为了展示常量定义的一些细节。我们定义了一个`__ARR__`常量。
`ZVAL_NEW_PERSISTENT_ARR(&c.value);`我们想让`__ARR__为持久的。所以使用`ZVAL_NEW_PERSISTENT_ARR`创建一个数组。
数组创建完后，我们需要初始化。初始化的代码就是

```C
Zend_hash_init(Z_ARRVAL(c.value), 0, NULL,(dtor_func_t)say_entry_dtor_persistent, 1);
```

参数中的`say_entry_dtor_persistent`是一个析构函数，用于释放数组的元素。

到这里，如果编译运行。当程序执行结束的时候，你会发现一个致命错误。

因为在程序执行完毕，内部zval释放的时候，会进行类型检测。如果发现是array object或者resources，则会报错。可以查看[Zend/zend_variables.c](https://github.com/php/php-src/blob/PHP-7.0.19/Zend/zend_variables.h)文件中_zval_internal_dtor方法。
为了解决这个问题，我们需要手动释放我们创建的__ARR__相关的数组。
模块卸载时执行的方法，是优先Zend内部zval释放方法之前调用的。因此，我们只要在`PHP_MSHUTDOWN_FUNCTION(demo)`方法中手动释放。不再让Zend去释放就可以解决了。

## 创建对象

### PHP 代码实现

```
<?php
$factory = new factory();
var_dump($factory->product);
$factory->production("love");
var_dump($factory->product);
?>

```

### C 代码实现

```C
zend_class_entry *demo_ce;

PHP_METHOD(factory, production);
ZEND_BEGIN_ARG_INFO_EX(arginfo_children_learn, 0, 0, 1)
ZEND_ARG_INFO(0, words)
ZEND_END_ARG_INFO()

const zend_function_entry children_methods[] = {
    PHP_ME(factory, production, arginfo_children_learn, ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
}

PHP_MINIT_FUNCTION(factory)
{
    zend_class_entry ce;
    INIT_CLASS_ENTRY(ce, "factory", children_methods);
    children_ce = zend_register_internal_class(&ce);
    zend_declare_property_null(demo_ce, "product",       sizeof("product") - 1, ZEND_ACC_PUBLIC);
}

PHP_METHOD(factory, production)
{
    char *words;
    size_t words_len;

    if (zend_parse_method_parameters(ZEND_NUM_ARGS(), getThis(), "s",&words, &words_len) == FAILURE) {
            return;
    }
    zend_update_property_string(children_ce,  getThis(), "product", sizeof("product") - 1, words);
}
```
