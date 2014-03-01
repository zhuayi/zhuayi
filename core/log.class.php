<?php
/**
 * log.class.php     Zhuayi log类
 *
 * @copyright    (C) 2005 - 2010  Zhuayi
 * @lastmodify   2010-10-27
 * @author       zhuayi
 * @QQ           2179942
 */
class log
{
    public static $log_file;

    public static $log_time = 3600;

    public static $log_data = array();
    public static $log_exception = array();

    public static function exception(Exception $e)
    {
        $strings = 'Uncaught '.get_class($e).', code: ' . $e->getCode() . "<br />Message: " . htmlentities($e->getMessage())."\n";
        return self::_set_log_data($strings,'exception');
    }

    public static function notice($strings)
    {
        $strings = "[NOTICE ".date("Y-m-d H:i:s")." ".self::get_debugback()."] ".$strings;
        return self::_set_log_data($strings);
    }

    static function get_debugback()
    {
        $debug_info = debug_backtrace();
        return "{$debug_info[2]['class']}->{$debug_info[2]['function']} line:{$debug_info[1]['line']}";
    }

    public static function _set_log_data($strings,$type = '')
    {
        if ($type == 'exception')
        {
            self::$log_exception[] = $strings;
        }
        else
        {
            self::$log_data[] = $strings;
        }
        return true;
    }

    public static function _create_uniqid()
    {
        global $pagestartime;
        $star_time = explode(" ", $pagestartime);
        if (php_sapi_name() !== 'cli')
        {
            $log_start[] = "urlid=".crc32($_SERVER['HTTP_COOKIE'].$_SERVER['REQUEST_URI']);
            $log_start[] = "url=".$_SERVER['REQUEST_URI'];
            $log_start[] = "cookie=".$_SERVER['HTTP_COOKIE'];
        }
        $log_start[] = "date=".date("Y-m-d H:i:s",$star_time[1]);

        if (!empty($_SERVER['HTTP_REFERER']))
        {
            $log_start[] = "referer=".$_SERVER['HTTP_REFERER'];
        }
        return "<".implode(' ', $log_start).">\n";
    }

    public static function _write_log()
    {
        $log_name = date("YmdHi",time() - time()%self::$log_time);
        $log_path = ZHUAYI_ROOT."/log/".APP_NAME."/";
        $log_file = $log_path.$log_name;

        /* 创建文件夹 */
        if (!is_dir($log_path))
        {
            $oldumask = umask(0);
            $reset = @mkdir($log_path,0777,true);
            if (!$reset)
            {
                throw new Exception("No such file or directory : mkdir {$log_path}", -1);
            }
            chmod($log_path, 0777);
        }

        if (!empty(self::$log_exception))
        {
            error_log(self::_create_uniqid().implode("\n",self::$log_exception),3,$log_file.".error");
        }
        return error_log(self::_create_uniqid().implode("\n",self::$log_data)."\n",3,$log_file.".log");
    }
}