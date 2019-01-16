# 用到的一些工具

## readelf

读出文件的 *.o的 ELF Header和Section Header Table

```bash
$ readelf -a max.o

ELF Header:
  Magic:   7f 45 4c 46 02 01 01 00 00 00 00 00 00 00 00 00
  Class:                             ELF64
  Data:                              2's complement, little endian
  Version:                           1 (current)
  OS/ABI:                            UNIX - System V
  ABI Version:                       0
  Type:                              REL (Relocatable file)
  Machine:                           Advanced Micro Devices X86-64
  Version:                           0x1
  Entry point address:               0x0
  Start of program headers:          0 (bytes into file)
  Start of section headers:          504 (bytes into file)
  Flags:                             0x0
  Size of this header:               64 (bytes)
  Size of program headers:           0 (bytes)
  Number of program headers:         0
  Size of section headers:           64 (bytes)
  Number of section headers:         8
  Section header string table index: 7
```
ELF Header中描述了操作系统是UNIX，体系结构是 Advanced Micro Devices X86-64。Section Header Table中有8个Section Header，从文件地址200（0xc8）开始，每个Section Header占40字节，共320字节，到文件地址0x207结束。这个目标文件没有Program Header。文件地址是这样定义的：文件开头第一个字节的地址是0，然后每个字节占一个地址。
从Section Header中读出各Section的描述信息，其中.text和.data是我们在汇编程序中声明的Section，而其它Section是汇编器自动添加的。Addr是这些段加载到内存中的地址（我们讲过程序中的地址都是虚拟地址），加载地址要在链接时填写，现在空缺，所以是全0。Off和Size两列指出了各Section的文件地址，比如.data段从文件地址0x60开始，一共0x38个字节，回去翻一下程序，.data段定义了14个4字节的整数，一共是56个字节，也就是0x38。根据以上信息可以描绘出整个目标文件的布局

## hexdump

hexdump主要用来查看“二进制”文件的十六进制编码。*注意：它能够查看任何文件，不限于与二进制文件。*

```bash
hexdump [选项] [文件]…

选项

-n length：格式化输出文件的前length个字节
-C：输出规范的十六进制和ASCII码
-b：单字节八进制显示
-c：单字节字符显示
-d：双字节十进制显示
-o：双字节八进制显示
-x：双字节十六进制显示
-s：从偏移量开始输出
-e 指定格式字符串，格式字符串由单引号包含，格式字符串形如：’a/b “format1” “format2”。每个格式字符串由三部分组成，每个由空格分割，如a/b表示，b表示对每b个输入字节应用format1格式，a表示对每个a输入字节应用format2，一般a>b，且b只能为1,2,4，另外a可以省略，省略a=1。format1和format2中可以使用类似printf的格斯字符串。
%02d：两位十进制
%03x：三位十六进制
%02o：两位八进制
%c：单个字符等
%_ad：标记下一个输出字节的序号，用十进制表示
%_ax：标记下一个输出字节的序号，用十六进制表示
%_ao：标记下一个输出字节的序号，用八进制表示
%_p：对不能以常规字符显示的用.代替
同一行显示多个格式字符串，可以跟多个-e选项
```

操作|命令
---|---
格式化输出文件|hexdump test
格式化输出文件的前10个字节|hexdump -n 10 test
格式化输出文件的前10个字节，并以16进制显示|hexdump -n 10 -C test
格式化输出从10开始的10个字节，并以16进制显示|hexdump -n 10 -C -s 20
格式化输出文件字符|hexdump -e ‘16/1 “%02X ” ” | “’ -e ‘16/1 “%_p” “\n”’ test     hexdump -e ‘1/1 “0x%08_ax “’ -e ‘8/1 “%02X ” ” * “’ -e ‘8/1 “%_p” “\n”’ test    hexdump -e ‘1/1 “%02_ad# “’ -e ‘/1 “hex = %02X * “’ -e ‘/1 “dec = %03d | “’ -e ‘/1 “oct = %03o”’ -e ‘/1 ” _\n”’ -n 20 test


## objdump

objdump命令是Linux下的反汇编目标文件或者可执行文件的命令，它还有其他作用，下面以ELF格式可执行文件test为例详细介绍：

命令|详情
---|---
objdump -f test|显示test的文件头信息
objdump -d test|反汇编test中的需要执行指令的那些section
objdump -D test|与-d类似，但反汇编test中的所有section
objdump -h test|显示test的Section Header信息
objdump -x test|显示test的全部Header信息
objdump -s test|除了显示test的全部Header信息，还显示他们对应的十六进制文件代码