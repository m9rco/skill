
这种请看 按v'1146
B.1. 服务器错误代码和消息 

服务器错误信息来自下述源文件： 


· 错误消息信息列在share/errmsg.txt文件中。“%d”和“%s”分别代表编号和字符串，显示时，它们将被消息值取代。 


· 错误值列在share/errmsg.txt文件中，用于生成include/mysqld_error.h和include/mysqld_ername.h MySQL源文件中的定义。 



· SQLSTATE值列在share/errmsg.txt文件中，用于生成include/sql_state.h MySQL源文件中的定义。 


由于更新很频繁，这些文件中可能包含这里未列出的额外错误消息。 


· 错误：1000 SQLSTATE: HY000 (ER_HASHCHK) 


消息：hashchk 


· 错误：1001 SQLSTATE: HY000 (ER_NISAMCHK) 


消息：isamchk 


· 错误：1002 SQLSTATE: HY000 (ER_NO) 


消息：NO 


· 错误：1003 SQLSTATE: HY000 (ER_YES) 


消息：YES 


· 错误：1004 SQLSTATE: HY000 (ER_CANT_CREATE_FILE) 


消息：无法创建文件'%s' (errno: %d) 


· 错误：1005 SQLSTATE: HY000 (ER_CANT_CREATE_TABLE) 


消息：无法创建表'%s' (errno: %d) 


· 错误：1006 SQLSTATE: HY000 (ER_CANT_CREATE_DB) 


消息：无法创建数据库'%s' (errno: %d) 


· 错误：1007 SQLSTATE: HY000 (ER_DB_CREATE_EXISTS) 


消息：无法创建数据库'%s'，数据库已存在。 


· 错误：1008 SQLSTATE: HY000 (ER_DB_DROP_EXISTS) 


消息：无法撤销数据库'%s'，数据库不存在。 


· 错误：1009 SQLSTATE: HY000 (ER_DB_DROP_DELETE) 


消息：撤销数据库时出错（无法删除'%s'，errno: %d） 


· 错误：1010 SQLSTATE: HY000 (ER_DB_DROP_RMDIR) 


消息：撤销数据库时出错（can't rmdir '%s', errno: %d） 


· 错误：1011 SQLSTATE: HY000 (ER_CANT_DELETE_FILE) 


消息：删除'%s'时出错 (errno: %d) 


· 错误：1012 SQLSTATE: HY000 (ER_CANT_FIND_SYSTEM_REC) 


消息：无法读取系统表中的记录。 


· 错误：1013 SQLSTATE: HY000 (ER_CANT_GET_STAT) 


消息：无法获取'%s'的状态(errno: %d) 


· 错误：1014 SQLSTATE: HY000 (ER_CANT_GET_WD) 


消息：无法获得工作目录(errno: %d) 


· 错误：1015 SQLSTATE: HY000 (ER_CANT_LOCK) 


消息：无法锁定文件(errno: %d) 


· 错误：1016 SQLSTATE: HY000 (ER_CANT_OPEN_FILE) 


消息：无法打开文件：'%s' (errno: %d) 


· 错误：1017 SQLSTATE: HY000 (ER_FILE_NOT_FOUND) 


消息：无法找到文件： '%s' (errno: %d) 


· 错误：1018 SQLSTATE: HY000 (ER_CANT_READ_DIR) 


消息：无法读取'%s'的目录 (errno: %d) 


· 错误：1019 SQLSTATE: HY000 (ER_CANT_SET_WD) 


消息：无法为'%s'更改目录 (errno: %d) 


· 错误：1020 SQLSTATE: HY000 (ER_CHECKREAD) 


消息：自上次读取以来表'%s'中的记录已改变。 


· 错误：1021 SQLSTATE: HY000 (ER_DISK_FULL) 


消息：磁盘满(%s)；等待某人释放一些空间... 


· 错误：1022 SQLSTATE: 23000 (ER_DUP_KEY) 


消息：无法写入；复制表'%s'的 键。 


· 错误：1023 SQLSTATE: HY000 (ER_ERROR_ON_CLOSE) 


消息：关闭'%s'时出错 (errno: %d) 


· 错误：1024 SQLSTATE: HY000 (ER_ERROR_ON_READ) 


消息：读取文件'%s'时出错 (errno: %d) 


· 错误：1025 SQLSTATE: HY000 (ER_ERROR_ON_RENAME) 


消息：将'%s'重命名为'%s'时出错 (errno: %d) 


· 错误：1026 SQLSTATE: HY000 (ER_ERROR_ON_WRITE) 


消息：写入文件'%s'时出错 (errno: %d) 


· 错误：1027 SQLSTATE: HY000 (ER_FILE_USED) 


消息：'%s'已锁定，拒绝更改。 


· 错误：1028 SQLSTATE: HY000 (ER_FILSORT_ABORT) 


消息：分类失败 


· 错误：1029 SQLSTATE: HY000 (ER_FORM_NOT_FOUND) 


消息：对于'%s'，视图'%s'不存在。 


· 错误：1030 SQLSTATE: HY000 (ER_GET_ERRNO) 


消息：从存储引擎中获得错误%d。 


· 错误：1031 SQLSTATE: HY000 (ER_ILLEGAL_HA) 


消息：关于'%s'的表存储引擎不含该选项。 


· 错误：1032 SQLSTATE: HY000 (ER_KEY_NOT_FOUND) 


消息：无法在'%s'中找到记录。 


· 错误：1033 SQLSTATE: HY000 (ER_NOT_FORM_FILE) 


消息：文件中的不正确信息：'%s' 


· 错误：1034 SQLSTATE: HY000 (ER_NOT_KEYFILE) 


消息：对于表'%s'， 键文件不正确，请尝试修复。 


· 错误：1035 SQLSTATE: HY000 (ER_OLD_KEYFILE) 


消息：旧的键文件，对于表'%s'，请修复之！ 


· 错误：1036 SQLSTATE: HY000 (ER_OPEN_AS_READONLY) 


消息：表'%s'是只读的。 


· 错误：1037 SQLSTATE: HY001 (ER_OUTOFMEMORY) 


消息：内存溢出，重启服务器并再次尝试（需要%d字节）。 


· 错误：1038 SQLSTATE: HY001 (ER_OUT_OF_SORTMEMORY) 


消息：分类内存溢出，增加服务器的分类缓冲区大小。 


· 错误：1039 SQLSTATE: HY000 (ER_UNEXPECTED_EOF) 


消息：读取文件'%s'时出现意外EOF (errno: %d) 


· 错误：1040 SQLSTATE: 08004 (ER_CON_COUNT_ERROR) 


消息：连接过多。 


· 错误：1041 SQLSTATE: HY000 (ER_OUT_OF_RESOURCES) 


消息：内存溢出，请检查是否mysqld或其他进程使用了所有可用内存，如不然，或许应使用'ulimit'允许mysqld使用更多内存，或增加交换空间的大小。 


· 错误：1042 SQLSTATE: 08S01 (ER_BAD_HOST_ERROR) 


消息：无法获得该地址给出的主机名。 


· 错误：1043 SQLSTATE: 08S01 (ER_HANDSHAKE_ERROR) 


消息：不良握手 


· 错误：1044 SQLSTATE: 42000 (ER_DBACCESS_DENIED_ERROR) 


消息：拒绝用户'%s'@'%s'访问数据库'%s'。 


· 错误：1045 SQLSTATE: 28000 (ER_ACCESS_DENIED_ERROR) 


消息：拒绝用户'%s'@'%s'的访问（使用密码：%s） 


· 错误：1046 SQLSTATE: 3D000 (ER_NO_DB_ERROR) 


消息：未选择数据库。 


· 错误：1047 SQLSTATE: 08S01 (ER_UNKNOWN_COM_ERROR) 


消息：未知命令。 


· 错误：1048 SQLSTATE: 23000 (ER_BAD_NULL_ERROR) 


消息：列'%s'不能为空。 


· 错误：1049 SQLSTATE: 42000 (ER_BAD_DB_ERROR) 


消息：未知数据库'%s'。 


· 错误：1050 SQLSTATE: 42S01 (ER_TABLE_EXISTS_ERROR) 


消息：表'%s'已存在。 


· 错误：1051 SQLSTATE: 42S02 (ER_BAD_TABLE_ERROR) 


消息：未知表'%s'。 


· 错误：1052 SQLSTATE: 23000 (ER_NON_UNIQ_ERROR) 


消息：%s中的列'%s'不明确。 


· 错误：1053 SQLSTATE: 08S01 (ER_SERVER_SHUTDOWN) 


消息：在操作过程中服务器关闭。 


· 错误：1054 SQLSTATE: 42S22 (ER_BAD_FIELD_ERROR) 


消息：'%s'中的未知列'%s'。 


· 错误：1055 SQLSTATE: 42000 (ER_WRONG_FIELD_WITH_GROUP) 


消息：'%s'不在GROUP BY中。 


· 错误：1056 SQLSTATE: 42000 (ER_WRONG_GROUP_FIELD) 


消息：无法在'%s'上创建组。 


· 错误：1057 SQLSTATE: 42000 (ER_WRONG_SUM_SELECT) 


消息：语句中有sum函数和相同语句中的列。 


· 错误：1058 SQLSTATE: 21S01 (ER_WRONG_VALUE_COUNT) 


消息：列计数不匹配值计数。 


· 错误：1059 SQLSTATE: 42000 (ER_TOO_LONG_IDENT) 


消息：ID名称'%s'过长。 


· 错误：1060 SQLSTATE: 42S21 (ER_DUP_FIELDNAME) 


消息：重复列名'%s'。 


· 错误：1061 SQLSTATE: 42000 (ER_DUP_KEYNAME) 


消息：重复键名称'%s'。 


· 错误：1062 SQLSTATE: 23000 (ER_DUP_ENTRY) 


消息：键%d的重复条目'%s'。 


· 错误：1063 SQLSTATE: 42000 (ER_WRONG_FIELD_SPEC) 


消息：对于列'%s'，列分类符不正确。 


· 错误：1064 SQLSTATE: 42000 (ER_PARSE_ERROR) 


消息：在行%d上，%s靠近'%s'。 


· 错误：1065 SQLSTATE: 42000 (ER_EMPTY_QUERY) 


消息：查询为空。 
