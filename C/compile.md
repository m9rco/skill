# C语言编译过程


## C程序编译步骤

C代码编译成可执行程序经过4步：

1. *预处理*：宏定义展开、头文件展开、条件编译等，同时将代码中的注释删除，这里并不会检查语法
2. *编译*：检查语法，将预处理后文件编译生成汇编文件
3. *汇编*：将汇编文件生成目标文件(二进制文件)
4. *链接*：C语言写的程序是需要依赖各种库的，所以编译之后还需要把库链接到最终的可执行程序中去

```
flow
st=>start: Start|past:>http://www.google.com[blank]
e=>end: End:>http://www.google.com
op1=>operation: get_hotel_ids|past
op2=>operation: get_proxy|current
sub1=>subroutine: get_proxy|current
op3=>operation: save_comment|current
op4=>operation: set_sentiment|current
op5=>operation: set_record|current

cond1=>condition: ids_remain空?
cond2=>condition: proxy_list空?
cond3=>condition: ids_got空?
cond4=>condition: 爬取成功??
cond5=>condition: ids_remain空?

io1=>inputoutput: ids-remain
io2=>inputoutput: proxy_list
io3=>inputoutput: ids-got

st->op1(right)->io1->cond1
cond1(yes)->sub1->io2->cond2
cond2(no)->op3
cond2(yes)->sub1
cond1(no)->op3->cond4
cond4(yes)->io3->cond3
cond4(no)->io1
cond3(no)->op4
cond3(yes, right)->cond5
cond5(yes)->op5
cond5(no)->cond3
op5->e
```
