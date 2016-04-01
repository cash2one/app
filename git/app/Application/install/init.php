<?php
/**
 * 用法 php init.php web根目录路径 [r]
 * 
 * web根目录路径使用相对路径或绝对路径均可
 * r选项可选，用于恢复配置
 *
 * 使用示例：
 * 1.移动配置文件到根目录：
 * sudo php init.php .
 * sudo php init.php /data/wwwroot/seven
 * 2.恢复配置至初始状态
 * sudo php init.php . r
 * sudo php init.php /data/wwwroot/seven
 *
 * 注意：Nginx配置中需要禁止Conf目录极其文件直接访问
 */
error_reporting(0);
set_time_limit(0);

if ($argc < 2) {
	exit('use: php init.php web_root_path [r]');
}


define('CONF_PATH_NAME', 'Conf'); // liuliu 2016-1-13 添加统一配置文件夹常量
define('APP_PATH', $argv[1] . '/Application/');
define('CONF_PATH', $argv[1] . '/' . CONF_PATH_NAME . '/');

//php init.php path r   还原
if ($argc === 3) {
	if ($argv[2] === 'r') {
		$di = new DirectoryIterator(APP_PATH);
		foreach ($di as $f) {
			$name = $f->getFilename();
			if (!$f->isDot() && $f->isDir()) {
				if (!is_writable(APP_PATH . $name)) {
					exit('no write access');
				}

				if (file_exists(APP_PATH . $name . '/Conf/config.php.bak')) {
					file_put_contents(APP_PATH . $name . '/Conf/config.php', file_get_contents(APP_PATH . $name . '/Conf/config.php.bak')) || exit('write config file failed');//恢复
				}
			}
		}
	} else {
        exit('unknown option');
    }

	exit('success!');
}

if (!is_writable('.')) {
	exit('no write access');
}

if (!is_dir(CONF_PATH)) {
	if (!mkdir(CONF_PATH, '0775')) {
		exit(CONF_PATH . 'create failed');
	}
}

$di = new DirectoryIterator(APP_PATH);
foreach ($di as $f) {
	$name = $f->getFilename();
	if (!$f->isDot() && $f->isDir()) {		
		if (file_exists(APP_PATH . $name . '/Conf/config.php')) {

			//备份
			if (!file_exists(APP_PATH . $name . '/Conf/config.php.bak')) {
				file_put_contents(APP_PATH . $name . '/Conf/config.php.bak', file_get_contents(APP_PATH . $name . '/Conf/config.php')) || exit('create bak file failed');
			}
            
			// 判断是否已写入 liuliu 2016-1-13
            $old_path = APP_PATH . $name . '/Conf/config.php';
            $old_content = file_get_contents($old_path);
            if (empty($old_content) || strpos($old_content,'return include')!==False) {
				continue;
            }

            // 写入新配置文件
			if(!file_put_contents(CONF_PATH . $name . '.php', $old_content)) {
				exit(CONF_PATH . $name . '.php write failed');
			} else {
				echo CONF_PATH . $name . '.php write success'. PHP_EOL;
                //更改原配置文件
				file_put_contents($old_path, '<?php if(file_exists("./' . CONF_PATH_NAME . '/' . $name . '.php")) {return include "./' . CONF_PATH_NAME . '/' . $name . '.php";}');
			}
		}
	}
}