# 语法基础笔记  

## 基础结构

### 关键字

C 的关键字有`32`个

类型|说明
---|---
数据类型（12）| `char`  `short` `short` `int` `long` `float` `double` `unsigned` `signed` `struct` `union` `enum` `void`
控制语句（12）| `if` `else` `switch` `case` `default` `for` `do` `while` `break` `continue` `goto` `return`
存储类（5）|`auto` `extern` `register` `static` `const`
其他（3）|  `sizeof` `typedef` `volatile`

### 常量

常量（Constant）是程序中最基本的元素，有字符（Character）常量、整数（Integer）常量、浮点数（Floating Point）常量和枚举常量

```c
#define SYMBOL 1
```

### 变量

#### 可变变量

在内存中存取数据要明确三件事情：数据存储在哪里、数据的长度以及数据的处理方式。

```c
int a, b, c;
float m = 10.9, n = 20.56;
char p, q = '@';
```

#### 只读变量

```c
const float m = 10.9;
```
#### 声明和定义区别

 - 声明变量不需要建立存储空间，如：extern int variable;
 - 定义变量需要建立存储空间，如：int variable;

#### 在32位环境中，各种数据类型的长度一般如下

说 明|字符型 |短整型| 整型|  长整型 |单精度浮点型|  双精度浮点型
---|---|---|---|---|---|---
数据类型|    char|    short |  int| long|    float|   double
长  度  |  1   |2   |4   |4   |4   |8

#### sizeof 关键字

- sizeof不是函数，所以不需要包含任何头文件，它的功能是计算一个数据类型的大小，单位为字节
- sizeof的返回值为 `size_t`
- size_t类型在32位操作系统下是`unsigned int`，是一个无符号的整数

#### int 整型

打印格式	|含义
---|---
%d	|输出一个有符号的10进制int类型
%o(字母o)|	输出8进制的int类型
%x	|输出16进制的int类型，字母以小写输出
%X	|输出16进制的int类型，字母以大写写输出
%u	|输出一个10进制的无符号数

数据类型	|占用空间
---|---
short(短整型)	|2字节
int(整型)	|4字节
long(长整形)	|Windows为4字节，Linux为4字节(32位)，8字节(64位)
long long(长长整形)|	8字节


#### float、double  实型(浮点型)

 实型变量也可以称为浮点型变量，浮点型变量是用来存储小数数值的

数据类型	|占用空间	|有效数字范围
float	|4字节	|7位有效数字
double	|8字节	|15～16位有效数字

由于浮点型变量是由有限的存储单元组成的，因此只能提供有限的有效数字。在有效位以外的数字将被舍去，这样可能会产生一些误差。

> 不以f结尾的常量是double类型，以f结尾的常量(如3.14f)是float类型

```c
#include <stdio.h>

int main()
{
	//传统方式赋值
	float a = 3.14f; //或3.14F
	double b = 3.14;

	printf("a = %f\n", a);
	printf("b = %lf\n", b);

	//科学法赋值
	a = 3.2e3f; //3.2*1000 = 32000，e可以写E
	printf("a1 = %f\n", a);

	a = 100e-3f; //100*0.001 = 0.1
	printf("a2 = %f\n", a);

	a = 3.1415926f;
	printf("a3 = %f\n", a); //结果为3.141593

	return 0;
}
```

#### char 字符型

> char的本质就是一个1字节大小的整型。

字符型变量用于存储一个单一字符，在 C 语言中用 char 表示，其中每个字符变量都会占用 1 个字节。
在给字符型变量赋值时，需要用一对英文半角格式的单引号(' ')把字符括起来。

数据类型	|占用空间|	取值范围
---|---|---
char	|1字节|	-128到 127(-27 ~ 27-1)
unsigned char|1字节|	0 到 255(0 ~ 28-1)


#### 数组和字符串

> 数组就是在内存中连续的相同类型的变量空间。同一个数组所有的成员都是相同的数据类型，同时所有的成员在内存中的地址是连续的。

数组（Array）也是一种复合数据类型，它由一系列相同类型的元素（Element）组成。例如定义一个由4个int型元素组成的数组count：
```
int count[4];
```

##### 字符数组与字符串区别

- C语言中没有字符串这种数据类型，可以通过char的数组来替代；
- 字符串一定是一个char的数组，但char的数组未必是字符串；
- 数字0(和字符'\0'等价)结尾的char数组就是一个字符串，但如果char数组没有以数字0结尾，那么就不是一个字符串，只是普通字符数组，所以字符串是一种特殊的char的数组。

#### 类型转换

数据有不同的类型，不同类型数据之间进行混合运算时必然涉及到类型的转换问题。

转换的方法有两种：
 - 自动转换(隐式转换)：遵循一定的规则,由编译系统自动完成。
 - 强制类型转换：把表达式的运算结果强制转换成所需的数据类型。

类型转换的原则：占用内存字节数少(值域小)的类型，向占用内存字节数多(值域大)的类型转换，以保证精度不降低。

 ![conversion](./assets/type_conversion.jpg)

##### 隐式转换

```c
#include <stdio.h>

int main()
{
	int num = 5;
	printf("s1=%d\n", num / 2);
	printf("s2=%lf\n", num / 2.0);

	return 0;
}
```

##### 强制转换

强制类型转换指的是使用强制类型转换运算符，将一个变量或表达式转化成所需的类型，其基本语法格式如下所示：
(类型说明符) (表达式)

```c
#include <stdio.h>

int main()
{
	float x = 0;
	int i = 0;
	x = 3.6f;

	i = x;			//x为实型, i为整型，直接赋值会有警告
	i = (int)x;		//使用强制类型转换

	printf("x=%f, i=%d\n", x, i);

	return 0;
}
```

#### 数值溢出

当超过一个数据类型能够存放最大的范围时，数值会溢出。

有符号位最高位溢出的区别：符号位溢出会导致数的正负发生改变，但最高位的溢出会导致最高位丢失。

#### 类型限定符

限定符|	含义
---|---
extern|	声明一个变量，extern声明的变量没有建立存储空间。
extern| int a;
const|定义一个常量，常量的值不能修改。const int a = 10;
volatile|	防止编译器优化代码
register|	定义寄存器变量，提高效率。register是建议型的指令，而不是命令型的指令，如果CPU有空闲寄存器，那么register就生效，如果没有空闲寄存器，那么register无效。

#### printf函数和putchar函数

> printf是输出一个字符串，putchar输出一个char。

printf格式字符：

打印格式|	对应数据类型|	含义
%d|	int|	接受整数值并将它表示为有符号的十进制整数
%hd|	short int|	短整数
%hu|	unsigned short |	无符号短整数
%o	|unsigned int	|无符号8进制整数
%u|	unsigned int|	无符号10进制整数
%x,%X	|unsigned int|	无符号16进制整数，x对应的是abcdef，X对应的是ABCDEF
%f|	float|	单精度浮点数
%lf	|double|	双精度浮点数
%e,%E|	double	|科学计数法表示的数，此处"e"的大小写代表在输出时用的"e"的大小写
%c|	char|	字符型。可以把输入的数字按照ASCII码相应转换为对应的字符
%s|	char *| 	字符串。输出字符串中的字符直至字符串中的空字符（字符串以'\0'结尾，这个'\0'即空字符）
%p|	void *|	以16进制形式输出指针
%%|	%	|输出一个百分号

#### scanf函数与getchar函数

- getchar是从标准输入设备读取一个char。
- scanf通过%转义的方式可以得到用户通过标准输入设备输入的数据。


### 流程控制

C 的流程控制和大多数语言一致，此处请靠悟性解决

### 函数

#### 函数的声明

所谓函数声明，就是在函数尚在未定义的情况下，事先将该函数的有关信息通知编译系统，相当于告诉编译器，函数在后面定义，以便使编译能正常进行。

1. 定义是指对函数功能的确立，包括指定函数名、函数类型、形参及其类型、函数体等，它是一个完整的、独立的函数单位。
2. 声明的作用则是把函数的名字、函数类型以及形参的个数、类型和顺序(注意，不包括函数体)通知编译系统，以便在对包含函数调用的语句进行编译时，据此对其进行对照检查（例如函数名是否正确，实参与形参的类型和个数是否一致）。

> 注意：一个函数只能被定义一次，但可以声明多次。

```C
#include <stdio.h>

int max(int x, int y); // 函数的声明，分号不能省略
// int max(int, int); // 另一种方式

int main()
{
	int a = 10, b = 25, num_max = 0;
	num_max = max(a, b); // 函数的调用
	printf("num_max = %d\n", num_max);
	return 0;
}

// 函数的定义
int max(int x, int y)
{
	return x > y ? x : y;
}
```

#### main函数与exit函数

在main函数中调用exit和return结果是一样的，但在子函数中调用return只是代表子函数终止了，在子函数中调用exit，那么程序终止。

#### 作用域的demo

```c
#include <stdio.h>

int add_range(int low, int high)
{
    int i, sum;
    sum = 0; // 尝试注释掉这里
    for (i = low; i <= high; i++)
        sum = sum + i;
    return sum;
}

int main(void)
{
    int result[100];
    result[0] = add_range(1, 10);
    result[1] = add_range(1, 100);
    printf("result[0]=%d\nresult[1]=%d\n", result[0], result[1]);
    return 0;
}
```

#### 多文件(分文件)编程

 把函数声明放在头文件*.h中，在主函数中包含相应头文件，在头文件对应的*.c中实现*.h声明的函数
