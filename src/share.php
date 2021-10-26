<?php

/**
 * define_once
 * Will define a constant, but avoids the constant already defined error if that constant has already been defined.
 */

if (!function_exists('define_once')) {
    function define_once($constantName, $constantValue)
    {
        if (!defined($constantName)) {
            define($constantName, $constantValue);
        }
    }
}

/**
 * show
 * Outputs the passed-in data structure to the screen, detecting whether it is being displayed in the browser and
 * attempting to display in a 'pretty' style.
 */
if (!function_exists('show')) {
    function show($var, $label = null, $showType = true)
    {
        $http = isset($_SERVER['HTTP_USER_AGENT']) ? true : false;

        if ($http) {
            echo "<pre style=\"color: #000000; background-color: #C8C8C8; border: 1px solid #686868; padding: 3px; font-size: 8pt; text-align: left; max-height: 200px; overflow: auto; \">\n";
        }

        if (isset($label)) {
            print(($http) ? "<b>{$label}</b>: " : "{$label}: ") ;
        }

        $type = gettype($var);

        switch ($type) {
            case 'object':
            case 'array':
                if (isset($label)) {
                    echo "\n";
                }
                echo ($http ? htmlentities(print_r($var, 1)) : print_r($var, 1));
                break;

            case 'boolean':
            case 'bool':
                ($var === true) ? print("true\n") : print("false\n");
                break;

            case 'NULL':
                echo "null\n";
                break;

            default:
                if ($showType) { echo "($type) "; }
                echo str_replace("\t", ' ', ($http ? htmlentities($var) : $var) ) . "\n";
                break;
        }

        if ($http) {
            echo "</pre>\n";
        }
    }
}

/**
 * dump
 * Dump the passed-in data structure to the error log
 */
if (!function_exists('dump')) {
    function dump($stuff, $label = null)
    {
        $log = '';
        if (!is_null($label)) {
            $log .= $label . ': ';
        }
        error_log($log . print_r($stuff, 1));
    }
}

/**
 * debug_output
 * Write the specified message to the debug log if the DEBUG constant has been set to TRUE
 */
if (!function_exists('debug_output')) {
    function debug_output($message)
    {
        if (defined('DEBUG') && DEBUG) debug_log($message);
    }
}

/**
 * debug_log
 * Write the specified message to the debug log
 */
if (!function_exists('debug_log')) {
    function debug_log($message)
    {
        error_log($message, 3, '/var/log/php5/debug.log');
    }
}

/**
 * func_trace
 * Takes the output of a debug_backtrace, formats it into something readable, and returns an array
 */
if (!function_exists('func_trace')) {
    function func_trace()
    {
        $trace = debug_backtrace();
        // get rid of the call to this function
        array_shift($trace);
        $light = array();
        foreach ($trace as $entry) {
            $str = '';
            if (array_key_exists('file', $entry)) {
                $str .= $entry['file'];
                if (array_key_exists('line', $entry)) {
                    $str .= ':' . $entry['line'];
                }
                $str .= ';';
            }
            if (array_key_exists('class', $entry)) {
                $str .= $entry['class'] . $entry['type'];
            }
            $str .= $entry['function'] . '()';

            $light[] = $str;
        }

        return $light;
    }
}

if (!function_exists('point')) {
    function point()
    {
        static $point = 0;
        return $point++;
    }
}


if (!function_exists('tlstart')) {
    function tlstart($key)
    {
        TimeLog::get($key)->start();
    }
}

if (!function_exists('tllog')) {
    function tllog($key)
    {
        TimeLog::get($key)->log();
    }
}

if (!function_exists('tlstop')) {
    function tlstop($key)
    {
        TimeLog::get($key)->stop();
    }
}

class TimeLog
{
    private static $_instance = array();

    private $_start;

    private $_key;

    private $_i = 0;

    public static function get($key)
    {
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new self($key);
        }
        return self::$_instance[$key];
    }

    protected function __construct($key)
    {
        $this->_key = $key;
    }

    public function start()
    {
        $this->_start = microtime(true);
        $this->_i = 0;
        error_log("{$this->_key} start");
    }

    public function log()
    {
        error_log("{$this->_key} " . ++$this->_i . ': ' . number_format(microtime(true) - $this->_start, 3) . 's');
    }

    public function stop()
    {
        error_log($this->_key . ' end: ' . number_format(microtime(true) - $this->_start, 3) . 's');
    }
}

/**
 * XHProf Profile flags
 * If the xhprof extension is installed profiling can be switched on by adding the GET parameter xhprofProfile=enable
 * to the URL, and can be disabled again using xhprofProfile=disable. This expects that the xhprof UI will be available
 * from the xhprof_html/ directory in the site, so the profiler isn't profiled as well.
 */
$enFile = (isset($_SERVER['HTTP_HOST']))
    ? '/tmp/xhprof.' . $_SERVER['HTTP_HOST'] . '.enable'
    : '/tmp/xhprof.script.enable';
if (isset($_GET['xhprofProfile'])
        && in_array(strtolower($_GET['xhprofProfile']), array('disable', 'false', '0', 'off'))
        && file_exists($enFile)) {
    unlink($enFile);
}
$xhprofProfile = (
    (isset($_GET['xhprofProfile']) && in_array(strtolower($_GET['xhprofProfile']), array('enable', 'true', '1', 'on')))
        || file_exists($enFile)
) && !strstr($_SERVER['SCRIPT_FILENAME'], 'xhprof_html') && !isset($_SERVER['SHELL']);
if (extension_loaded('xhprof') && $xhprofProfile) {
    touch($enFile);
    $path = file_exists('/usr/share/pear/xhprof_lib')
        ? '/usr/share/pear/xhprof_lib/utils'
        : '/usr/share/php/xhprof_lib/utils';

    include_once $path . '/xhprof_lib.php';
    include_once $path . '/xhprof_runs.php';
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

