## 查看MYSQL中数据表占用的空间


1. 首先打开指定的数据库：
```sql
use information_schema;
```
2. 如果想看指定数据库中的数据表，可以用如下语句：
```
SELECT concat(round(sum(DATA_LENGTH/1024/1024),2),'MB') as data FROM TABLES WHERE TABLE_SCHEMA='databases' AND TABLE_NAME='table';
```
3. 如果想看数据库中每个数据表的，可以用如下语句：
```
SELECT TABLE_NAME,DATA_LENGTH+INDEX_LENGTH,TABLE_ROWS,concat(round((DATA_LENGTH+INDEX_LENGTH)/1024/1024,2), 'MB') as data FROM TABLES WHERE TABLE_SCHEMA='databases';
```

## 查询表中重复字段
```
SELECT reseach_album_id, COUNT(*) AS repeat_count FROM dat_bill_201811 GROUP BY reseach_album_id HAVING repeat_count > 1;
```
