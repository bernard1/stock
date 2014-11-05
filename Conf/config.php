<?php
return array(
    'URL_MODEL'	=>	2, // 如果你的环境不支持PATHINFO 请设置为3
    'DB_TYPE'	=>	'mysql',
    'DB_HOST'	=>	'localhost',
    'DB_NAME'	=>	'stock',
    'DB_USER'	=>	'root',
    'DB_PWD'	=>	'123123',
    'DB_PORT'	=>	'3306',
    'DB_PREFIX'	=>	'',
   // 'SHOW_PAGE_TRACE'           =>  1//显示调试信息
    'TEMPALTE_BASE_URL'=>'http://192.168.1.23/stock/',
    'CURRENCY_CODE' => 'CNY',
    'LOG_RECORD' => true,
    'LOG_LEVEL'=> 'EMERG,ALERT,CRIT,ERR,WARN,INFO,DEBUG,SQL',// 允许记录的日志级别,

);
