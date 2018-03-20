<?php
/**
 * 框架核心
 * @package Core
 */

/**
 * 設定檔讀取類
 */
class ConfigFile {

    /**
     * 讀檔
     * @param  string  $filename    檔案路徑
     * @param  boolean $parse_const 常量替換
     * @return mixed
     */
    public static function load($filename, $parse_const = false) {
		
		
        if (! $f = realpath($filename) and
            ! (strpos($filename, CONFIG_PATH) === false and
            $f = realpath(CONFIG_PATH.$filename))) {
            return false;
        }

        $filename = $f;
		
		
        $ext = substr(strrchr($filename, "."), 1);

        if ($ext === 'php') {
            return self::load_php($filename);
        }

        $func = 'parse_' . $ext;

        if (! method_exists(__CLASS__, $func)) {
            return false;
        }

        if (! $file = self::read_file($filename)) {
			
            return false;
        }
		
        $ret = call_user_func(array(__CLASS__, $func), $file);
        return $ret && $parse_const ? self::replace_const($ret) : $ret;
    }

    private static function load_php($filename) {
        ob_start();
        $ret = include($filename);
        ob_end_clean();
        return $ret;
    }

    private static function read_file($filename) {
        ## remove bom
        return preg_replace('/^\xef\xbb\xbf/', '', file_get_contents($filename));
    }

    /**
     * 解析 ini
     *
     * parse_ini_string 加強版
     *
     * @param  string $str ini字串
     * @return array / false
     */
    public static function parse_ini($str) {

        $lines = array_filter(array_map('trim', preg_split('#[\r\n]+#', (string)$str)));
        $ret = array();
        $section = false;

        foreach ($lines as $line) {

            if (preg_match('/^\[(.*)\]$/', $line, $match)) {
                $section = $match[1];
            } elseif (preg_match('/^((?![;#]).*?)\s*(\[(.*)\])?\s*=\s*(\'|"|)(.*?)\4$/', $line, $match)) {
                $key   = &$match[1];
                $isarr = &$match[2];
                $index = &$match[3];
                $value = &$match[5];

                if ($section === false) {
                    $tar = &$ret;
                } else {
                    empty($ret[$section]) and $ret[$section] = array();
                    $tar = &$ret[$section];
                }

                if ($isarr) {
                    empty($tar[$key]) and $tar[$key] = array();

                    if (strlen($index)) {
                        $tar[$key][$index] = $value;
                    } else {
                        $tar[$key][] = $value;
                    }
                } else {
                    $tar[$key] = $value;
                }
            }
        }

        return $ret;
    }

    /**
     * 解析 json
     *
     * origin: https://github.com/getify/JSON.minify
     *
     * @param  string $str json字串
     * @return array / false
     */
    public static function parse_json($json) {
        $tokenizer = "/\"|(\/\*)|(\*\/)|(\/\/)|\n|\r/";
        $in_string = false;
        $in_multiline_comment = false;
        $in_singleline_comment = false;
        $tmp;
        $tmp2;
        $new_str = array();
        $ns = 0;
        $from = 0;
        $lc;
        $rc;
        $lastIndex = 0;

        while (preg_match($tokenizer,$json,$tmp,PREG_OFFSET_CAPTURE,$lastIndex)) {
            $tmp = $tmp[0];
            $lastIndex = $tmp[1] + strlen($tmp[0]);
            $lc = substr($json,0,$lastIndex - strlen($tmp[0]));
            $rc = substr($json,$lastIndex);
            if (!$in_multiline_comment && !$in_singleline_comment) {
                $tmp2 = substr($lc,$from);
                if (!$in_string) {
                    $tmp2 = preg_replace("/(\n|\r|\s)*/","",$tmp2);
                }
                $new_str[] = $tmp2;
            }
            $from = $lastIndex;

            if ($tmp[0] == "\"" && !$in_multiline_comment && !$in_singleline_comment) {
                preg_match("/(\\\\)*$/",$lc,$tmp2);
                if (!$in_string || !$tmp2 || (strlen($tmp2[0]) % 2) == 0) { // start of string with ", or unescaped " character found to end string
                    $in_string = !$in_string;
                }
                $from--; // include " character in next catch
                $rc = substr($json,$from);
            }
            else if ($tmp[0] == "/*" && !$in_string && !$in_multiline_comment && !$in_singleline_comment) {
                $in_multiline_comment = true;
            }
            else if ($tmp[0] == "*/" && !$in_string && $in_multiline_comment && !$in_singleline_comment) {
                $in_multiline_comment = false;
            }
            else if ($tmp[0] == "//" && !$in_string && !$in_multiline_comment && !$in_singleline_comment) {
                $in_singleline_comment = true;
            }
            else if (($tmp[0] == "\n" || $tmp[0] == "\r") && !$in_string && !$in_multiline_comment && $in_singleline_comment) {
                $in_singleline_comment = false;
            }
            else if (!$in_multiline_comment && !$in_singleline_comment && !(preg_match("/\n|\r|\s/",$tmp[0]))) {
                $new_str[] = $tmp[0];
            }
        }
        $new_str[] = $rc;
        return json_decode(implode("",$new_str), true);
    }

    public static $const_format = '{([A-Z_]+)}';

    /**
     * 替換常量
     * 將 %UPPER_CASE% 格式的字串替換為常量
     *
     * @param  array  $conf [description]
     * @return [type]       [description]
     */
    public static function replace_const(array $conf) {
        $ret = array();

        foreach ($conf as $key => $value) {
            $key = preg_replace_callback('/'.self::$const_format.'/', __CLASS__.'::replace_const_callback', $key);

            $ret[$key] = is_array($value) ?
                        call_user_func(__METHOD__, $value) :
                        preg_replace_callback('/'.self::$const_format.'/', __CLASS__.'::replace_const_callback', $value);
        }

        return $ret;
    }

    public static function replace_const_callback($m) {
        return defined($m[1]) ? constant($m[1]) : $m[1];
    }


}