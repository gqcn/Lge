<?php
// 核心类
include(__DIR__.'/Core.func.php');  // 函数定义
include(__DIR__.'/Core.class.php'); // 框架总管
include(__DIR__.'/Base.class.php'); // 框架基础类

// 框架组件(核心框架组件手动记载，其他采用自动加载)
include(__DIR__.'/component/Data.class.php');         // 数据封装器
include(__DIR__.'/component/Config.class.php');       // 配置管理类
include(__DIR__.'/component/Router.class.php');       // 路由封装类
include(__DIR__.'/component/Logger.class.php');       // 日志封装类

// MMVC相关类包含(显示控制类在需要时自动加载)
include(__DIR__.'/model/BaseModel.class.php');
include(__DIR__.'/model/BaseModelTable.class.php');
include(__DIR__.'/module/BaseModule.class.php');
include(__DIR__.'/controller/BaseController.class.php');

/**
 * 设置默认的错误处理回调函数.
 */
set_error_handler(array('\Lge\Core', 'defaultErrorHandler'));

/**
 * 设置默认的异常处理回调函数.
 */
set_exception_handler(array('\Lge\Core', 'defaultExceptionHandler'));

/**
 * 类自动加载.
 */
spl_autoload_register(array('\Lge\Core', 'classAutoloader'));

/**
 * 框架初始化.
 */
\Lge\Core::init();
