<?php
//------------------------------------------------------------------------------
# 定義專案的根目錄，其他變數可以用這個變數來取絕對路徑
define('ROOT_PATH', dirname(__FILE__).DS);

# 定義設定檔的目錄，ConfigFile::load會以這個目錄為起始目錄
define('CONFIG_PATH', ROOT_PATH.'config/');

# 定義類別目錄
define('CLASS_PATH', ROOT_PATH . 'classes/');

# 定義核心類目錄
define('CORE_PATH', CLASS_PATH . 'core/');

# 定義controller目錄，可以自定義
define('CONTROLLER_PATH', CLASS_PATH.'controller'.DS);

# 定義樣板目錄，可以自定義
define('TPL_PATH', ROOT_PATH.'template'.DS);

# 定義controller後置詞
define('CONTROLLER_POSTFIX', '_Ctl');
//------------------------------------------------------------------------------
# 載入Autoloader
require(CORE_PATH.'autoloader.php');

# 註冊Autoloader
Autoloader::register();

# 設定include路徑
Autoloader::add_include_path(CORE_PATH);

# 設定include路徑
Autoloader::add_include_path(CLASS_PATH.'lib');

# 讀取autoloader.ini索引檔
Autoloader::add_classes(ConfigFile::load('autoloader.ini', true));
//------------------------------------------------------------------------------
# 設定home路由
Router::add(
    'home',
    '(<action>)',
    array (
        'controller' => 'home',
        'action' => 'index'
    ),
    array (
        'action'    => '[a-z0-9_]+'
    )
);

## 設定預設路由
Router::add(
    'default',
    '<module>/<controller>(/<action>)',
    array ('action' => 'index'),
    array (
        'module'    => '[a-z][a-z0-9]*',
        'controller'=> '[a-z0-9_]+',
        'action'    => '[a-z0-9_]+'
    )
);
//------------------------------------------------------------------------------
# 設定錯誤處理
ErrorHandler::init();
# 註冊錯誤處理方法
ErrorHandler::register_fatal_handler(function ($code, $type, $message, $file, $line)
{
    return PHP_View::make("error/showerror.php", array(
        "code" => $code,
        "type" => $type,
        "message" => $message,
        "file" => $file,
        "line" => $line
    ));
});
