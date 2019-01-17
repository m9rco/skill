# php 扩展开发笔记

从0至1开发PHP扩展学习笔记

   * [实现一个hello]('实现一个hello')
   * [传参与返回值](#传参与返回值)
   * [Installation](#installation)
   * [Usage](#usage)
      * [STDIN](#stdin)
      * [Local files](#local-files)
      * [Remote files](#remote-files)
      * [Multiple files](#multiple-files)
      * [Combo](#combo)
      * [Auto insert and update TOC](#auto-insert-and-update-toc)
      * [Github token](#github-token)
   * [Tests](#tests)
   * [Dependency](#dependency)


## 传参与返回值

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


