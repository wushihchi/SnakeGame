<?php

defined('E_DEPRECATED') or define('E_DEPRECATED', 8192);
defined('E_USER_DEPRECATED') or define('E_USER_DEPRECATED', 16384);
defined('E_FATAL_LEVEL') or define('E_FATAL_LEVEL', E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_USER_ERROR|E_RECOVERABLE_ERROR);

class ErrorHandler {

	public static $types = array(
		E_ERROR             => 'Fatal Error',
		E_WARNING           => 'Warning',
		E_PARSE             => 'Parsing Error',
		E_NOTICE            => 'Notice',
		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR     => 'Compile Error',
		E_COMPILE_WARNING   => 'Compile Warning',
		E_USER_ERROR        => 'User Error',
		E_USER_WARNING      => 'User Warning',
		E_USER_NOTICE       => 'User Notice',
		E_STRICT            => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Runtime Recoverable error',
		E_DEPRECATED        => 'Runtime Deprecated code usage',
		E_USER_DEPRECATED   => 'User Deprecated code usage',
	);

    private static $initialized;
    private static $warnings = array();
    private static $fatal_handler;
    private static $warning_handler;

    public static function error_handler($errno, $errstr, $errfile, $errline ) {
        if($errno & E_FATAL_LEVEL) throw new ErrorException($errstr, $errno, 0, $errfile, $errline);

        $warn = array(
                'code' => $errno,
                'type' => self::$types[$errno],
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline
        );

        if (defined('DDEBUG')) {
            printf("<div><b>%s</b>(%d): %s in <b>%s</b> on line %d</div>\n", $warn['type'], $warn['code'], $warn['message'], $warn['file'], $warn['line']);
        } else {
            self::$warnings[] = $warn;
        }

        return true;
    }

    public static function shutdown_function() {
        error_reporting(0);
        restore_error_handler();
        if ($error = error_get_last() AND $error['type'] & E_FATAL_LEVEL) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            $error['code'] = $error['type'];
            $error['type'] = self::$types[$error['code']];
            $handler = !defined('DDEBUG') && is_callable(self::$fatal_handler) ? self::$fatal_handler : __CLASS__.'::show_error';
            if ( ! headers_sent()) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
            }
            try {
                echo call_user_func_array($handler, array($error['code'], $error['type'], $error['message'], $error['file'], $error['line']));
            } catch (Exception $e) {
                echo self::show_error($error['code'], $error['type'], $error['message'], $error['file'], $error['line']);
            }
        } else {
            if ( ! empty(self::$warnings) and is_callable(self::$warning_handler)) {
                call_user_func(self::$warning_handler, self::$warnings);
            }
        }
    }

    public static function register_fatal_handler($callable) {
        self::$fatal_handler = $callable;
    }

    public static function register_warning_handler($callable) {
        self::$warning_handler = $callable;
    }

    private static function show_error($code, $type, $message, $file, $line) {
        $buffer = array();
        $buffer[] = '<html>';
        $buffer[] = '<head>';
        $buffer[] = '<meta charset="UTF-8">';
        $buffer[] = '<title>500 Internal Server Error</title>';
        $buffer[] = '</head>';
        $buffer[] = '<body>';
        $buffer[] = '<h1>500 Internal Server Error</h1>';
        $buffer[] = '<div>';
        $buffer[] = sprintf("<b>%s</b>(%d): %s in <b>%s</b> on line %d", $type, $code, $message, $file, $line);
        $buffer[] = '</div>';
        $buffer[] = '</body>';
        $buffer[] = '</html>';
        return implode("\n", $buffer);
    }

    public static function init() {
        if(self::$initialized) {
             throw new ErrorException('ErrorHandler has been initialized already!');
        } else {
            register_shutdown_function(__CLASS__.'::shutdown_function');
            set_error_handler(__CLASS__.'::error_handler');
            self::$initialized = true;
        }
    }
}
