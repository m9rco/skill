pgSQL 里一般日志打在这里打
/var/lib/pgsql/9.5/data/pg_log
启动作pgsql
/usr/pgsql-9.5/bin/pg_ctl -D /var/lib/pgsql/9.5/data start
重启作pgsql
/usr/pgsql-9.5/bin/pg_ctl -D /var/lib/pgsql/9.5/data reload
关闭作pgsql
/usr/pgsql-9.5/bin/pg_ctl -D /var/lib/pgsql/9.5/data stop
pgsql 配置文件
/var/lib/pgsql/9.5/data/pg_hba.conf
