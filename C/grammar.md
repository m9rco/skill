# 语法基础笔记  
- [Abstract](#abstract)  
- [Preface](#preface)  
- [Design idea and innovation point](#design-idea-and-innovation-point)  
  - [Background](#background) 
  - [Design inspirations](#design-inspirations) 
  - [Innovation point](#innovation-point)  
  - [Developmental vision](#developmental-vision) 



### 基础结构

##### 常量

常量（Constant）是程序中最基本的元素，有字符（Character）常量、整数（Integer）常量、浮点数（Floating Point）常量和枚举常量

```c
#define SYMBOL 1
```

##### 变量

在内存中存取数据要明确三件事情：数据存储在哪里、数据的长度以及数据的处理方式。

变量名不仅仅是为数据起了一个好记的名字，还告诉我们数据存储在哪里，使用数据时，只要提供变量名即可；而数据类型则指明了数据的长度和处理方式。所以诸如int n;、char c;、float money;这样的形式就确定了数据在内存中的所有要素。


```c
int a, b, c;
float m = 10.9, n = 20.56;
char p, q = '@';
```

在32位环境中，各种数据类型的长度一般如下：

说 明|字符型 |短整型| 整型|  长整型 |单精度浮点型|  双精度浮点型
---|---|---|---|---|---|---
数据类型|    char|    short |  int| long|    float|   double
长  度  |  1   |2   |4   |4   |4   |8


### 函数

一段极其优秀的C代码，快速了解C的作用域


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


##### GCC


GCC指令|用途
---|---
gcc -E main.c|看到预处理之后、编译之前的程序
gcc -Wall main.c && a.out|执行一下编译后的程序
