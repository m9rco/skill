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



## 什么是扩展？
如果你用过PHP，那么你就用过扩展。除了一些极少的特殊情况之外，PHP语言中的每个用户空间函数都是以组的形式分布在一个或多个扩展之中。这些函数中的大部分是位于标准扩展中的 – 总共超过400个。
PHP源码中包含86个扩展，平均每个扩展中有30个函数。算一下，大概有2500个函数。如果这个不够用，[PECL](http://pecl.php.net/)仓库还提供了超过100个其他扩展，或者还可以在互联网上找到更多的扩展。

PHP除了扩展中的这些函数之外，剩下的是什么」我听到了你的疑问「扩展是什么？PHP的核心又是什么？」
PHP的核心是由两个独立的部分组成的。在最底层是Zend Engine (ZE)。ZE 负责把人类可以理解的脚本解析成机器可以理解的符号（token），然后在一个进程空间内执行这些符号。ZE还负责内存管理，变量作用域，以及函数调用的调度。另一部分是PHP。PHP负责与SAPI层（Server Application Programming Interface，经常被用来与Apache, IIS, CLI, CGI等host环境进行关联）的交互以及绑定。它也为safe_mode和open_basedir检查提供了一个统一的控制层，就像streams层把文件和网络I/O与用户空间函数（例如fopen()，fread()和fwrite()）关联起来一样。

## 生命周期

当一个给定的SAPI启动后，以/usr/local/apache/bin/apachectl start的响应为例，PHP便以初始化它的核心子系统作为开始。随着SAPI启动程序的结束，PHP开始加载每个扩展的代码，然后调用它们的模块初始化(MINIT)程序。这就给每个扩展机会用来初始化内部变量，申请资源，注册资源处理器，并且用ZE注册自己的函数，这样如果一个脚本调用这些函数中的一个，ZE就知道执行哪些代码。

接下来，PHP会等待SAPI层的页面处理请求。在CGI或者CLI SAPI情况下，这个请求会立即发生并且只执行一次。在Apache, IIS, 或者其他成熟的web服务器SAPI中，请求处理会在远程用户发起请求的时候发生，并且会重复执行很多次，也可能是并发的。不管请求是怎么进来的，PHP以让ZE来建立脚本可以运行的环境作为开始，然后调用每个扩展的请求初始化（RINIT）函数。RINIT给了扩展一个机会，让其可以建立指定的环境变量，分配请求指定的资源，或者执行其他任务例如审计。关于RINIT函数调用最典型的例子是在session扩展中，如果session.auto_start选项是开启的，RINIT会自动触发用户空间的session_start()函数并且预先填充$_SESSION变量。

当请求一旦被初始化，ZE便把PHP脚本翻译成符号（token），最终翻译成可以进行单步调试和执行的opcode。如果这些opcode中的一个需要调用一个扩展函数，ZE将会给那个函数绑定参数，并且临时放弃控制权直到函数执行完成。

当一个脚本完成了执行之后，PHP将会调用每个扩展的请求结束(RSHUTDOWN)函数来执行最后的清理工作（比如保存session变量到磁盘上）。接下来，ZE执行一个清理过程（熟知的垃圾回收），实际上是对上次请求过程中使用的变量调用unset()函数。

一旦完成，PHP等待SAPI发起另一个文档请求或者一个关闭信号。在CGI和CLI SAPI的情况下，没有所谓的“下一个请求”，所以SAPI会立刻执行关闭流程。在关闭过程中，PHP又让每个扩展调用自己的模块关闭（MSHUTDOWN）函数，最后关闭自己的核心子系统。

这个过程第一次听令人有些费解，但是一旦你深入到一个扩展的开发过程中，它就会逐渐的清晰起来。

## 内存分配

为了避免写的很糟糕的扩展泄露内存，ZE以自己内部的方式来进行内存管理，通过用一个附加的标志来指明持久化。一个持久化分配的内存比单个页面请求存在的时间要长。一个非持久化分配的内存，相比之下，在请求结束的时候就会被释放，不管free函数是否被调用。例如用户空间变量，都是非持久化分配的内存，因为在请求结束之后这些变量都没有用了。

一个扩展理论上可以依靠ZE在每个页面请求结束后自动释放非持久化的内存，但这是不被推荐的。在请求结束的时候，分配的内存不会被立即被回收，并且会持续一段时间，所以和那块内存关联的资源将不会被恰当的关闭，这是一个很糟的做法，因为如果不能适当的清理的话，这会产生混乱。就像你即将要看见的，确定所有分配的数据被恰当的清除了是非常的简单。

让我们把常规的内存分配函数（只应该当和内部库一起工作的时候才会用到）和PHP ZE中的持久化和非持久化内存分配函数进行一个对比。

## 开发环境
现在你已经掌握了一些关于PHP和ZE的工作原理，我估计你希望要深入进去，并且开始写些什么。无论如何在你能做之前，你需要收集一些必要的开发工具，并且建立一个满足自己目标的环境。

第一你需要PHP本身，以及构建PHP所需要的开发工具集合。如果你对于从源码编译PHP不熟悉，我建议你看看http://www.php.net/install.unix。(开发windows下的PHP扩展在以后的文章会介绍)。使用适合自己发行版的PHP二进制包是很诱人的，但是这些版本总是会忽略两个重要的

选项，这两个选项在开发过程中非常方便。第一个是--enable-debug。这个选项将会用附加符号信息来编译PHP所以，如果一个段错误发生，那么你将可以从PHP收集到一个核心dump信息，然后使用gdb来跟踪这个段错误是在哪里发生的，为什么会发生。另一个选项依赖于你将要进行扩展开发的PHP版本。在PHP4.3这个选项叫--enable-experimental-zts，在PHP5和以后的版本中叫--enable-maintainer-zts。这个选项将会让PHP思考在多线程环境中的行为，并且可以让你捕获常见的程序错误，这些错误在非线程环境中不会引起问题，但在多线程环境中却使你的扩展变得不可用。一旦你已经使用这些额外的选项编译好了PHP，并且已经安装在了你的开发服务器（或者工作站）上，那么你可以开始建立你的第一个扩展了。


## hello m9rco

### PHP 代码实现

```php
function say(){
    echo 'hello m9rco'
};
```

现在你将会把这个逻辑放到一个PHP扩展中。

首先让我们在你PHP源码树的ext/目录下创建一个名叫demo的目录，并进入(chdir)到这个目录中。
这个目录实际上可以放在任何地方，PHP源码树内或者PHP源码树外，但是我希望你把它放在源码树内为了接下来的文章使用。

在这你需要创建三个文件：
    - 一个包含你 `say`函数的源文件
    - 一个头文件，其中包含PHP加载你扩展时候所需的引用
    - 一个配置文件，它会被phpize用来准备扩展的编译环境。

### 使用ext_skel生成代码

PHP为我们提供了生成基本代码的工具 ext_skel。这个工具在PHP源代码的./ext目录下。
```
$ cd php_src/ext/
$ ./ext_skel --extname=demo
```
### 修改config.m4配置文件

去掉PHP_ARG_WITH相关代码的注释。否则，去掉 PHP_ARG_ENABLE 相关代码段的注释。我们编写的扩展不需要依赖其他的扩展和lib库

### 代码实现

修改demo.c文件。实现say方法。

找到PHP_FUNCTION(confirm_say_compiled)，在其上面增加如下代码：
```
PHP_FUNCTION(demo)
{
        zend_string *strg;
        strg = strpprintf(0, "hello m9rco");
        RETURN_STR(strg);
}
```
找到 PHP_FE(confirm_say_compiled, 在上面增加如下代码：

`PHP_FE(demo, NULL)`
`
修改后的代码如下：
```
const zend_function_entry say_functions[] = {
     PHP_FE(demo, NULL)       /* For testing, remove later. */
     PHP_FE(confirm_say_compiled,    NULL)       /* For testing, remove later. */
     PHP_FE_END  /* Must be the last line in say_functions[] */
 };
 /* }}} */
```

### 编译安装

```
$ phpize
$ ./configure
$ make && make install
```

修改`php.ini`文件，增加如下代码： `php -i|grep .ini`

```
[demo]
extension = demo.so
```

然后执行，php -m 命令。在输出的内容中，你会看到say字样。


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

## 配置项

### 声明变量

```h
// 定义一个全局变量类型，代码在`php_demo.h`文件中

ZEND_BEGIN_MODULE_GLOBALS(demo)
zend_long  global_number;
char *global_string;
zend_bool global_boolean;
ZEND_END_MODULE_GLOBALS(demo)

//声明一个全局变量
ZEND_DECLARE_MODULE_GLOBALS(demo)
```
### 设置配置项
所有的配置项参数都必须在PHP_INI_BEGIN() 和 PHP_INI_END()之间

```
PHP_INI_BEGIN()
    STD_PHP_INI_ENTRY("demo.number", "100", PHP_INI_ALL, OnUpdateLong, global_value, zend_say_globals, say_globals)
    STD_PHP_INI_ENTRY("demo.string", "ab", PHP_INI_ALL, OnUpdateString, global_string, zend_say_globals, say_globals)
    STD_PHP_INI_ENTRY("demo.boolean", "0", PHP_INI_ALL, OnUpdateBool, global_string, zend_say_globals, say_globals)
PHP_INI_END()
```

### 加载配置项

这一步主要是把配置项从配置文件中读取出来，根据第二步设置的参数，赋值给第一步声明的变量。
宏方法`REGISTER_INI_ENTRIES();`是用于加载配置文件的。这个宏方法默认是被注释掉，在`PHP_MINIT_FUNCTION`方法中。只要把注释给去掉即可。

```
PHP_MINIT_FUNCTION(demo)
{
    ......
    REGISTER_INI_ENTRIES();
}
```

### 读取配置项

在PHP扩展中读取配置项值，需要使用一个宏方法SAY_G()。这个宏方法定义在php_say.h中。
现在，我们定义一个方法show_ini()来显示配置项内容。代码如下：
```
PHP_FUNCTION(show_ini)
{
    zval arr;
    array_init(&arr);
    add_assoc_long_ex(&arr, "demo.number", 10, SAY_G(global_number));
    add_assoc_string_ex(&arr, "demo.string", 10, SAY_G(global_string));
    add_assoc_bool_ex(&arr, "demo.boolean", 11, SAY_G(global_boolean));
    RETURN_ZVAL(&arr, 0, 1);
}
```

### 销毁配置项

这一步主要是为了在PHP进程结束时，释放配置项占用的资源。
销毁配置项是通过宏方法`UNREGISTER_INI_ENTRIES()`来实现的。这个方法默认在`PHP_MSHUTDOWN_FUNCTION`方法中。
默认是被注释掉的。只要把注释去掉就可以了。代码如下：

```
PHP_MSHUTDOWN_FUNCTION(demo)
{
    UNREGISTER_INI_ENTRIES();
   ......
}
```

