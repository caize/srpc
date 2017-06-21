#/bin/sh
path=`dirname $0`;
processName=`/usr/local/php7/bin/php ${path}/getSwooleProcessName.php 'namepre'`;
if [[ $1 == 'restart' ]]; then
    count=0;
else
    count=`ps -fe | grep -i ${processName} | grep  -v 'grep' | grep "master" | wc -l`
fi;
if [ $count -lt 1 ]; then
	ps -eaf | grep -i ${processName} | grep  -v 'grep' | awk '{print $2}' | xargs kill -9
	sleep 2
	ulimit -c unlimited
	#test
	`/usr/local/php7/bin/php ${path}/../server/swoole.php`
	/usr/local/php7/bin/php ${path}/../server/swooleTcpRpc.php
	#/usr/local/bin/php /var/www/webrpc/server/swoole.php
fi;
