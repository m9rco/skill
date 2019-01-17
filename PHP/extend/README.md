# php 扩展开发笔记

## 环境准备

###  实现一个hello world

1. 克隆 php 官方源码

```bash
git clone  --depth=1 --branch PHP-7.0.19 https://github.com/php/php-src.git
```

2. 生成代码
PHP为我们提供了生成基本代码的工具 ext_skel。这个工具在PHP源代码的./ext目录下。

```
$ cd php_src/ext/
$ ./ext_skel --extname=say
```
extname参数的值就是扩展名称。执行ext_skel命令后，这样在当前目录下会生成一个与扩展名一样的目录。

```
Creating directory say
Creating basic files: config.m4 config.w32 .gitignore say.c php_say.h CREDITS EXPERIMENTAL tests/001.phpt say.php [done].

To use your new extension, you will have to execute the following steps:

1.  $ cd ..
2.  $ vi ext/say/config.m4
3.  $ ./buildconf
4.  $ ./configure --[with|enable]-say
5.  $ make
6.  $ ./sapi/cli/php -f ext/say/say.php
7.  $ vi ext/say/say.c
8.  $ make

Repeat steps 3-6 until you are satisfied with ext/say/config.m4 and
step 6 confirms that your module is compiled into PHP. Then, start writing
code and repeat the last two steps as often as necessary.
```

3. 修改config.m4配置文件
config.m4的作用就是配合phpize工具生成configure文件。configure文件是用于环境检测的。检测扩展编译运行所需的环境是否满足。现在我们开始修改config.m4文件。

```
$ cd ./say
$ vim ./config.m4
```

打开，config.m4文件后，你会发现这样一段文字。

```
dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(say, for say support,
dnl Make sure that the comment is aligned:
dnl [  --with-say             Include say support])

dnl Otherwise use enable:

dnl PHP_ARG_ENABLE(say, whether to enable say support,
dnl Make sure that the comment is aligned:
dnl [  --enable-say           Enable say support])
```

其中，dnl 是注释符号。上面的代码说，如果你所编写的扩展如果依赖其它的扩展或者lib库，需要去掉PHP_ARG_WITH相关代码的注释。否则，去掉 PHP_ARG_ENABLE 相关代码段的注释。我们编写的扩展不需要依赖其他的扩展和lib库。因此，我们去掉PHP_ARG_ENABLE前面的注释。去掉注释后的代码如下：

```
dnl If your extension references something external, use with:

 dnl PHP_ARG_WITH(say, for say support,
 dnl Make sure that the comment is aligned:
 dnl [  --with-say             Include say support])

 dnl Otherwise use enable:

 PHP_ARG_ENABLE(say, whether to enable say support,
 Make sure that the comment is aligned:
 [  --enable-say           Enable say support])
```

4. 代码实现
修改say.c文件。实现say方法。
找到PHP_FUNCTION(confirm_say_compiled)，在其上面增加如下代码：

```
PHP_FUNCTION(say)
{
        zend_string *strg;
        strg = strpprintf(0, "hello word");
        RETURN_STR(strg);
}
```
找到 PHP_FE(confirm_say_compiled, 在上面增加如下代码：

```
PHP_FE(say, NULL)

```
const zend_function_entry say_functions[] = {
     PHP_FE(say, NULL)       /* For testing, remove later. */
     PHP_FE(confirm_say_compiled,    NULL)       /* For testing, remove later. */
     PHP_FE_END  /* Must be the last line in say_functions[] */
 };
 /* }}} */

```
5. 切换至php-src目录
```
$ cd php-src
```

5. 编译安装
编译扩展的步骤如下：

```bash
$ phpize
$ ./configure
$ make && make install
```
修改php.ini文件，增加如下代码：

```bash
[say]
extension = say.so
然后执行，php -m 命令。在输出的内容中，你会看到say字样。
```

## 参考资料
- [hello world](https://www.bo56.com/php7%E6%89%A9%E5%B1%95%E5%BC%80%E5%8F%91%E4%B9%8Bhello-word/)