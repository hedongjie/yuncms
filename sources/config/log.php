<?php
/**
 * log config
 */
return array (
		'log_threshold' => 4, // 0-4 0关闭日志 1 错误信息 2 Debug信息 3 报告信息 4 所有信息
		'log_path' => DATA_PATH . 'logs' . DIRECTORY_SEPARATOR,//日志存储路径
		'log_date_format' => 'Y-m-d H:i:s',// 日志日期格式
		'log_chunk_size' => '2M',// 日志块大小
);