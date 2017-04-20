#!/bin/sh
#重启脚本
#$2 进程名 默认master，可选 manager，worker，task
path=`dirname $0`;
processName=`/usr/local/php7/bin/php ${path}/getSwooleProcessName.php $2`;
sig='USR1';
sigDesc="reload";
if [[ $1 == 'TERM' ]];
then
    sig=$1;
    sigDesc="stop";
elif [[ $1 == 'USR2' ]];then
    sig=$1;
    sigDesc=" task reload";
fi;
echo "swoole ${sigDesc} start ....\n"
`pidof ${processName} | xargs kill -${sig}`
echo "swoole ${sigDesc} end \n";
