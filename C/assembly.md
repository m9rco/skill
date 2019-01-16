# 汇编

## 最简单的汇编程序

```Assembly
#PURPOSE: Simple program that exits and returns a
#	  status code back to the Linux kernel
#
#INPUT:   none
#
#OUTPUT:  returns a status code. This can be viewed
#	  by typing
#
#	  echo $?
#
#	  after running the program
#
#VARIABLES:
#	  %eax holds the system call number
#	  %ebx holds the return status
#
 .section .data

 .section .text
 .globl _start
_start:
 movl $1, %eax	# this is the linux kernel command
		# number (system call) for exiting
		# a program

 movl $4, %ebx	# this is the status number we will
		# return to the operating system.
		# Change this around and it will
		# return different things to
		# echo $?

 int $0x80	# this wakes up the kernel to run
		# the exit command
```

把这个程序保存成文件hello.s（汇编程序通常以.s作为文件名后缀），用汇编器（Assembler）as把汇编程序中的助记符翻译成机器指令，生成目标文件hello.o：

```bash
as hello.s -o hello.o
```

然后用链接器（Linker，或Link Editor）ld把目标文件hello.o链接成可执行文件hello：

```bash
ld hello.o -o hello
```
链接主要有两个作用，一是修改目标文件中的信息，对地址做重定位，二是把多个目标文件合并成一个可执行文件，我们这个例子虽然只有一个目标文件，但也需要经过链接才能成为可执行文件。


下面逐行分析这个汇编程序。首先，#号表示单行注释，类似于C语言的//注释。

汇编程序中以.开头的名称并不是指令的助记符，不会被翻译成机器指令，而是给汇编器一些特殊指示，称为汇编指示（Assembler Directive）或伪操作（Pseudo-operation），由于它不是真正的指令所以加个“伪”字。


.section指示把代码划分成若干个段（Section），程序被操作系统加载执行时，每个段被加载到不同的地址，操作系统对不同的页面设置不同的读、写、执行权限。
.data段保存程序的数据，是可读可写的，相当于C程序的全局变量。本程序中没有定义数据，所以.data段是空的。

```Assembly
 .section .data
```
.text段保存代码，是只读和可执行的，后面那些指令都属于.text段。

```Assembly
 .globl _start
```

_start是一个符号（Symbol），符号在汇编程序中代表一个地址，可以用在指令中，汇编程序经过汇编器的处理之后，所有的符号都被替换成它所代表的地址值。在C语言中我们通过变量名访问一个变量，其实就是读写某个地址的内存单元，我们通过函数名调用一个函数，其实就是跳转到该函数第一条指令所在的地址，所以变量名和函数名都是符号，本质上是代表内存地址的。

.globl指示告诉汇编器，_start这个符号要被链接器用到，所以要在目标文件的符号表中标记它是一个全局符号。

_start就像C程序的main函数一样特殊，是整个程序的入口，链接器在链接时会查找目标文件中的_start符号代表的地址，把它设置为整个程序的入口地址，所以每个汇编程序都要提供一个_start符号并且用.globl声明。

如果一个符号没有用.globl声明，就表示这个符号不会被链接器用到。

```Assembly
_start:
```

这里定义了_start符号，汇编器在翻译汇编程序时会计算每个数据对象和每条指令的地址，当看到这样一个符号定义时，就把它后面一条指令的地址作为这个符号所代表的地址。

而_start这个符号又比较特殊，它所代表的地址是整个程序的入口地址，所以下一条指令movl $1, %eax就成了程序中第一条被执行的指令。

```Assembly
 movl $1, %eax
````

这是一条数据传送指令，这条指令要求CPU内部产生一个数字1并保存到eax寄存器中。mov的后缀l表示long，说明是32位的传送指令。
这条指令不要求CPU读内存，1这个数是在CPU内部产生的，称为立即数（Immediate）。在汇编程序中，立即数前面要加$，寄存器名前面要加%，以便跟符号名区分开。
以后我们会看到mov指令还有另外几种形式，但数据传送方向都是一样的，第一个操作数总是源操作数，第二个操作数总是目标操作数。

```Assembly
 movl $4, %ebx
```

和上一条指令类似，生成一个立即数4并保存到ebx寄存器中。

```Assembly
 int $0x80
```

前两条指令都是为这条指令做准备的，执行这条指令时发生以下动作：

1. int指令称为软中断指令，可以用这条指令故意产生一个异常，上一章讲过，异常的处理和中断类似，CPU从用户模式切换到特权模式，然后跳转到内核代码中执行异常处理程序。
2. int指令中的立即数0x80是一个参数，在异常处理程序中要根据这个参数决定如何处理，在Linux内核中int $0x80这种异常称为系统调用（System Call）。内核提供了很多系统服务供用户程序使用，但这些系统服务不能像库函数（比如printf）那样调用，因为在执行用户程序时CPU处于用户模式，不能直接调用内核函数，所以需要通过系统调用切换CPU模式，经由异常处理程序进入内核，用户程序只能通过寄存器传几个参数，之后就要按内核设计好的代码路线走，而不能由用户程序随心所欲，想调哪个内核函数就调哪个内核函数，这样可以保证系统服务被安全地调用。在调用结束之后，CPU再切换回用户模式，继续执行int $0x80的下一条指令，在用户程序看来就像函数调用和返回一样。
3. eax和ebx的值是传递给系统调用的两个参数。eax的值是系统调用号，Linux的各种系统调用都是由int $0x80指令引发的，内核需要通过eax判断用户要调哪个系统调用，_exit的系统调用号是1。ebx的值是传给_exit的参数，表示退出状态。大多数系统调用完成之后会返回用户空间继续执行后面的指令，而_exit系统调用比较特殊，它会终止掉当前进程，而不是返回用户空间继续执行。


## x86的寄存器

x86的通用寄存器有`eax`、`ebx`、`ecx`、`edx`、`edi`、`esi`。这些寄存器在大多数指令中是可以任意选用的，比如`movl`指令可以把一个立即数传送到`eax`中，也可传送到`ebx`中。但也有一些指令规定只能用其中某个寄存器做某种用途，例如除法指令`idiv`l要求被除数在`eax`寄存器中，`edx`寄存器必须是0，而除数可以在任意寄存器中，计算结果的商数保存在eax寄存器中（覆盖原来的被除数），余数保存在edx寄存器中。也就是说，通用寄存器对于某些特殊指令来说也不是通用的。
x86的特殊寄存器有`ebp`、`esp`、`eip`、`eflags`。`eip`是程序计数器，`eflags`保存着计算过程中产生的标志位，在intel的手册中这几个标志位分别称为CF、OF、ZF、SF。ebp和esp用于维护函数调用的栈帧

## 求一组数最大值的汇编程序

```Assembly
#PURPOSE: This program finds the maximum number of a
#	  set of data items.
#
#VARIABLES: The registers have the following uses:
#
# %edi - Holds the index of the data item being examined
# %ebx - Largest data item found
# %eax - Current data item
#
# The following memory locations are used:
#
# data_items - contains the item data. A 0 is used
# to terminate the data
#
 .section .data
data_items: 		#These are the data items
 .long 3,67,34,222,45,75,54,34,44,33,22,11,66,0

 .section .text
 .globl _start
_start:
 movl $0, %edi  	# move 0 into the index register
 movl data_items(,%edi,4), %eax # load the first byte of data
 movl %eax, %ebx 	# since this is the first item, %eax is
			# the biggest

start_loop: 		# start loop
 cmpl $0, %eax  	# check to see if we've hit the end
 je loop_exit
 incl %edi 		# load next value
 movl data_items(,%edi,4), %eax
 cmpl %ebx, %eax 	# compare values
 jle start_loop 	# jump to loop beginning if the new
 			# one isn't bigger
 movl %eax, %ebx 	# move the value as the largest
 jmp start_loop 	# jump to loop beginning

loop_exit:
 # %ebx is the status code for the _exit system call
 # and it already has the maximum number
 movl $1, %eax  	#1 is the _exit() syscall
 int $0x80
```

### 汇编、链接、运行：

```bash
$ as max.s -o max.o
$ ld max.o -o max
$ ./max
$ echo $?
```

### 这个程序在一组数中找到一个最大的数，并把它作为程序的退出状态。这组数在.data段给出：


```Assembly
data_items:
 .long 3,67,34,222,45,75,54,34,44,33,22,11,66,0
```

.long指示声明一组数，每个数占32位，相当于C语言中的数组。这个数组开头定义了一个符号data_items，汇编器会把数组的首地址作为data_items符号所代表的地址，data_items类似于C语言中的数组名。data_items这个标号没有用.globl声明，因为它只在这个汇编程序内部使用，链接器不需要用到这个名字。除了.long之外，常用的数据声明还有：
- .byte，也是声明一组数，每个数占8位
- .ascii，例如.ascii "Hello world"，声明11个数，取值为相应字符的ASCII码。注意，和C语言不同，这样声明的字符串末尾是没有'\0'字符的，如果需要以'\0'结尾可以声明为.ascii "Hello world\0"。

data_items数组的最后一个数是0，我们在一个循环中依次比较每个数，碰到0的时候让循环终止。在这个循环中：
- edi寄存器保存数组中的当前位置，每次比较完一个数就把edi的值加1，指向数组中的下一个数。
- ebx寄存器保存到目前为止找到的最大值，如果发现有更大的数就更新ebx的值。
- eax寄存器保存当前要比较的数，每次更新edi之后，就把下一个数读到eax中。

```Assembly
_start:
 movl $0, %edi
```

初始化edi，指向数组的第0个元素。

```Assembly
 movl data_items(,%edi,4), %eax
```

这条指令把数组的第0个元素传送到eax寄存器中。data_items是数组的首地址，edi的值是数组的下标，4表示数组的每个元素占4字节，那么数组中第edi个元素的地址应该是data_items + edi * 4，写在指令中就是data_items(,%edi,4)，这种地址表示方式在下一节还会详细解释。

```Assembly
 movl %eax, %ebx
```

ebx的初始值也是数组的第0个元素。下面我们进入一个循环，循环的开头定义一个符号start_loop，循环的末尾之后定义一个符号loop_exit。

```Assembly
start_loop:
 cmpl $0, %eax
 je loop_exit
```

比较eax的值是不是0，如果是0就说明到达数组末尾了，就要跳出循环。cmpl指令将两个操作数相减，但计算结果并不保存，只是根据计算结果改变eflags寄存器中的标志位。如果两个操作数相等，则计算结果为0，eflags中的ZF位置1。je是一个条件跳转指令，它检查eflags中的ZF位，ZF位为1则发生跳转，ZF位为0则不跳转，继续执行下一条指令。可见比较指令和条件跳转指令是配合使用的，前者改变标志位，后者根据标志位决定是否跳转。je可以理解成“jump if equal”，如果参与比较的两数相等则跳转。

```Assembly
 incl %edi
 movl data_items(,%edi,4), %eax
```

将edi的值加1，把数组中的下一个数传送到eax寄存器中。

```Assembly
 cmpl %ebx, %eax
 jle start_loop
```

把当前数组元素eax和目前为止找到的最大值ebx做比较，如果前者小于等于后者，则最大值没有变，跳转到循环开头比较下一个数，否则继续执行下一条指令。jle表示“jump if less than or equal”。

```Assembly
 movl %eax, %ebx
 jmp start_loop
```

更新了最大值ebx然后跳转到循环开头比较下一个数。jmp是一个无条件跳转指令，什么条件也不判断，直接跳转。loop_exit符号后面的指令调_exit系统调用退出程序。


## 寻址方式

内存寻址在指令中可以表示成如下的通用格式：
```Assembly
ADDRESS_OR_OFFSET(%BASE_OR_OFFSET,%INDEX,MULTIPLIER)
```
它所表示的地址可以这样计算出来：
FINAL ADDRESS = ADDRESS_OR_OFFSET + BASE_OR_OFFSET + MULTIPLIER * INDEX
其中ADDRESS_OR_OFFSET和MULTIPLIER必须是常数，BASE_OR_OFFSET和INDEX必须是寄存器。在有些寻址方式中会省略这4项中的某些项，相当于这些项是0。

1. 直接寻址（Direct Addressing Mode）。只使用ADDRESS_OR_OFFSET寻址，例如movl ADDRESS, %eax把ADDRESS地址处的32位数传送到eax寄存器。
2. 变址寻址（Indexed Addressing Mode） 。上一节的movl data_items(,%edi,4), %eax就属于这种寻址方式，用于访问数组元素比较方便。
3. 间接寻址（Indirect Addressing Mode）。只使用BASE_OR_OFFSET寻址，例如movl (%eax), %ebx，把eax寄存器的值看作地址，把内存中这个地址处的32位数传送到ebx寄存器。注意和movl %eax, %ebx区分开。
4. 基址寻址（Base Pointer Addressing Mode）。只使用ADDRESS_OR_OFFSET和BASE_OR_OFFSET寻址，例如movl 4(%eax), %ebx，用于访问结构体成员比较方便，例如一个结构体的基地址保存在eax寄存器中，其中一个成员在结构体内的偏移量是4字节，要把这个成员读上来就可以用这条指令。
5. 立即数寻址（Immediate Mode）。就是指令中有一个操作数是立即数，例如movl $12, %eax中的$12，这其实跟寻址没什么关系，但也算作一种寻址方式。
6. 寄存器寻址（Register Addressing Mode）。就是指令中有一个操作数是寄存器，例如movl $12, %eax中的%eax，这跟内存寻址没什么关系，但也算作一种寻址方式。在汇编程序中寄存器用助记符来表示，在机器指令中则要用几个Bit表示寄存器的编号，这几个Bit也可以看作寄存器的地址，但是和内存地址不在一个地址空间。