

## 核心原则
- 选择对的存储引擎
- 表结构设计不留坑
- 及早发现性能瓶颈

## 存储引擎
- 首选InnoDb
- TokuDb MyRocks
  - 海量日志、采集数据
  - 需要压缩的压缩数据
- columnStore
  - 基于MariaDb的OLAP应用
 
## 表结构设计

- InnoDB表一定要有主键 
- 主键最好是INT/BIGINT，并呈单调递增特性 
- 要注意字符集/校验集 致性，避免造成类型隐式转换 
- 将TEXT/BLOB等 对象 独 存放新表中 
- 多 INT，少 CHAR


```
SHOW TABLE STATUS;

yejr@imysql.com [test]>show table status like ‘yejr'\G
*************************** 1. row ***************************
Name: yejr
Engine: InnoDB
Version: 10
Row_format: Dynamic
Avg_row_length: 70
Rows: 797626
Data_length: 56164352
Max_data_length: 0
Index_length: 13123584
Data_free: 279969792
Auto_increment: 893210
Create_time: 2017-12-02 20:57:36
Update_time: NULL
 Check_time: NULL
Collation: utf8_general_ci
Checksum: NULL
Create_options:
Comment:
```
- 查看表宽度 
- 计算碎片率

## 发现性能瓶颈

```
SELECT * FROM INFORMATION_SCHEMA.INNODB_TRX
mysql> select * from information_schema.innodb_trx\G
*************************** 1. row ***************************
trx_id: 150458138
trx_state: RUNNING
trx_started: 2017-12-10 16:17:18
trx_weight: 1038029
trx_mysql_thread_id: 48620
...
trx_tables_in_use: 0
trx_tables_locked: 1
trx_lock_structs: 14029
trx_lock_memory_bytes: 1597648
      trx_rows_locked: 1038028
    trx_rows_modified: 1024000
...
---TRANSACTION 150458138, ACTIVE 329 sec
14029 lock struct(s), heap size 1597648, 1038028 row lock(s), undo log entries 1024000
MySQL thread id 48620, OS thread handle 139902140466944, query id 3967568190 localhost root starting

```
- 找到锁定/修改最多rows的事务 
- 找到活跃时间最久的事务

## 发现性能瓶颈

```
slow query log
# User@Host: root[root] @ localhost []  Id:   504
# Schema: test  Last_errno: 0  Killed: 0
# Query_time: 0.018041  Lock_time: 0.000189  Rows_sent: 1  Rows_examined: 102400  Rows_affected: 0
# Bytes_sent: 68  Tmp_tables: 0  Tmp_disk_tables: 0  Tmp_table_sizes: 0
# QC_Hit: No Full_scan: Yes Full_join: No Tmp_table: No Tmp_table_on_disk: No
# Filesort: No Filesort_on_disk: No Merge_passes: 0
#   InnoDB_IO_r_ops: 0  InnoDB_IO_r_bytes: 0  InnoDB_IO_r_wait: 0.000000
#   InnoDB_rec_lock_wait: 0.000000  InnoDB_queue_wait: 0.000000
# InnoDB_pages_distinct: 175
SET timestamp=1512654670;
select count(*) from t1;
```

- 尤其是 `InnoDB_pages_distinct`
