<?php

include 'auto.php';
if(IS_SAE)
header("Location: index_sae.php");

if (file_exists('./install.lock')) {
    echo '
		<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>
        	你已经安装过该系统，如果想重新安装，请先删除站点Install目录下的 install.lock 文件，然后再安装。
        </body>
        </html>';
    exit;
}
@set_time_limit(1000);
if (phpversion() <= '5.3.0')
    set_magic_quotes_runtime(0);
if ('5.3.0' > phpversion()){
	header("Content-type:text/html;charset=utf-8");
	exit('您的php版本过低，不能安装本软件，请升级到5.3.0或更高版本再安装，谢谢！');
}

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: text/html; charset=UTF-8');
define('SITEDIR', _dir_path(substr(dirname(__FILE__), 0, -8)));
define("TP_SHOP_VERSION", '20160501');

//数据库
$sqlFile = 'tpshop.sql';
$configFile = 'config.php';
if (!file_exists(SITEDIR . 'Install/' . $sqlFile) || !file_exists(SITEDIR . 'Install/' . $configFile)) {
    echo '缺少必要的安装文件!';
    exit;
}
$Title = "TPshop安装向导";
$Powered = "Powered by TPshop";
$steps = array(
    '1' => '安装许可协议',
    '2' => '运行环境检测',
    '3' => '安装参数设置',
    '4' => '安装详细过程',
    '5' => '安装完成',
);
$step = isset($_GET['step']) ? $_GET['step'] : 1;

//地址
$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
$rootpath = @preg_replace("/\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if ((int) $_SERVER['SERVER_PORT'] != 80) {
    $domain .= ":" . $_SERVER['SERVER_PORT'];
}
$domain = $domain . $rootpath;

switch ($step) {

    case '1':
        include_once ("./templates/step1.php");
        exit();

    case '2':

        if (phpversion() < 5) {
            die('本系统需要PHP5+MYSQL >=5.0环境，当前PHP版本为：' . phpversion());
        }

        $phpv = @ phpversion();
        $os = PHP_OS;
        //$os = php_uname();
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $server = $_SERVER["SERVER_SOFTWARE"];
        $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');
		
        $err = 0;
        if (empty($tmp['GD Version'])) {
            $gd = '<font color=red>[×]Off</font>';
            $err++;
        } else {
            $gd = '<font color=green>[√]On</font> ' . $tmp['GD Version'];
        }
        if (function_exists('mysql_connect')) {
            $mysql = '<span class="correct_span">&radic;</span> 已安装';
        } else {
            $mysql = '<span class="correct_span error_span">&radic;</span> 出现错误';
            $err++;
        }
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 支持';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            $err++;
        }
        if(function_exists('curl_init')){
        	$curl = '<font color=green>[√]支持</font> ';
        }else{
        	$curl = '<font color=red>[×]不支持</font>';
        	$err++;
        }
        if(function_exists('file_put_contents')){
        	$file_put_contents = '<font color=green>[√]支持</font> ';
        }else{
        	$file_put_contents = '<font color=red>[×]不支持</font>';
        	$err++;
        }
        
        $folder = array(
            'Install',
        	'Public/upload',
        	'Application/Common/Conf',
            'Application/Runtime',
        	'Application/Runtime/Cache',
        	//'Application/Runtime/Data',
        	//'Application/Runtime/Logs',
        	'Application/Runtime/Temp',
        );
        include_once ("./templates/step2.php");
        exit();

    case '3':

        if ($_GET['testdbpwd']) {
            $dbHost = $_POST['dbHost'] . ':' . $_POST['dbPort'];
            $conn = @mysql_connect($dbHost, $_POST['dbUser'], $_POST['dbPwd']);
            if ($conn) {
                exit(json_encode(1));
            } else {
                die(json_encode(0));
            }
        }
        include_once ("./templates/step3.php");
        exit();


    case '4':
        if (intval($_GET['install'])) {
            $n = intval($_GET['n']);
            $arr = array();

            $dbHost = trim($_POST['dbhost']);
            $dbPort = trim($_POST['dbport']);
            $dbName = strtolower(trim($_POST['dbname']));
            $dbHost = empty($dbPort) || $dbPort == 3306 ? $dbHost : $dbHost . ':' . $dbPort;
            $dbUser = trim($_POST['dbuser']);
            $dbPwd = trim($_POST['dbpw']);
            $dbPrefix = empty($_POST['dbprefix']) ? 'tp_' : trim($_POST['dbprefix']);

            $username = trim($_POST['manager']);
            $password = trim($_POST['manager_pwd']);
            $email	  = trim($_POST['manager_email']);
            /*
            //网站名称
            $site_name = addslashes(trim($_POST['sitename']));
            //网站域名
            $site_url = trim($_POST['siteurl']);
            //描述
            $seo_description = addslashes(trim($_POST['siteinfo']));
            //关键词
            $seo_keywords = addslashes(trim($_POST['keywords']));
			*/
            $conn = @ mysql_connect($dbHost, $dbUser, $dbPwd);
            if (!$conn) {
                $arr['msg'] = "连接数据库失败!";           
                echo json_encode($arr);
                exit;
            }
            mysql_query("SET NAMES 'utf8'"); //,character_set_client=binary,sql_mode='';
            $version = mysql_get_server_info($conn);
            if ($version < 5.0) {
                $arr['msg'] = '数据库版本太低!';
                echo json_encode($arr);
                exit;
            }

            if (!mysql_select_db($dbName, $conn)) {
                //创建数据时同时设置编码
                if (!mysql_query("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;", $conn)) {
                    $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！';
                    echo json_encode($arr);
                    exit;
                }
                if ($n==-1) {
                    $arr['n'] = 0;
                    $arr['msg'] = "成功创建数据库:{$dbName}<br>";
                    echo json_encode($arr);
                    exit;
                }
                mysql_select_db($dbName, $conn);
            }

            //读取数据文件
            $sqldata = file_get_contents(SITEDIR . 'Install/' . $sqlFile);
            $sqlFormat = sql_split($sqldata, $dbPrefix);
            //创建写入sql数据库文件到库中 结束

            /**
             * 执行SQL语句
             */
            $counts = count($sqlFormat);

            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);

                if (strstr($sql, 'CREATE TABLE')) {
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    mysql_query("DROP TABLE IF EXISTS `$matches[1]");
                    $ret = mysql_query($sql);
                    if ($ret) {
                        $message = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成!<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li> ';
                    } else {
                        $message = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败!<span style="float: right;">'.date('Y-m-d H:i:s').'</span></li>';
                    }
                    $i++;
                    $arr = array('n' => $i, 'msg' => $message);
                    echo json_encode($arr);
                    exit;
                } else {
                    $ret = mysql_query($sql);
                    $message = '';
                    $arr = array('n' => $i, 'msg' => $message);
                    //echo json_encode($arr); exit;
                }
            }

            if ($i == 999999)
                exit;
            
            //读取配置文件，并替换真实配置数据1
            $strConfig = file_get_contents(SITEDIR . 'Install/' . $configFile);
            $strConfig = str_replace('#DB_HOST#', $dbHost, $strConfig);
            $strConfig = str_replace('#DB_NAME#', $dbName, $strConfig);
            $strConfig = str_replace('#DB_USER#', $dbUser, $strConfig);
            $strConfig = str_replace('#DB_PWD#', $dbPwd, $strConfig);
            $strConfig = str_replace('#DB_PORT#', $dbPort, $strConfig);
            $strConfig = str_replace('#DB_PREFIX#', $dbPrefix, $strConfig);
            $strConfig = str_replace('#DB_CHARSET#', 'utf8', $strConfig);
            $strConfig = str_replace('#DB_DEBUG#', false, $strConfig);
            @chmod(SITEDIR . '/Application/Common/Conf/db.php',0777); //数据库配置文件的地址
            @file_put_contents(SITEDIR . '/Application/Common/Conf/db.php', $strConfig); //数据库配置文件的地址
            
            //更新网站配置信息2
            /*
            $site_name = trim($_POST['sitename']);
            $site_url = trim($_POST['siteurl']);
            $site_keywords = trim($_POST['sitekeywords']);
            $site_desc = trim($_POST['siteinfo']);
            $sql = "INSERT INTO `{$dbPrefix}config` (name,value,inc_type) VALUES ('site_name','$site_name','shop_info'),('site_url','$site_url','shop_info'),('site_keywords','$site_keywords','shop_info'),('site_desc','$site_desc','shop_info')";
            mysql_query($sql);
            */
            //插入管理员表字段tp_admin表
            $time = time();
            $ip = get_client_ip();
            $ip = empty($ip) ? "0.0.0.0" : $ip;
            $password = md5('TPSHOP'.trim($_POST['manager_pwd']));
			mysql_query("delete from `{$dbPrefix}admin` where user_name = 'admin'");
			//mysql_query("INSERT INTO `{$dbPrefix}admin` (admin_id,user_name,email,password,add_time,last_ip,role_id) VALUES ('1','$username','$email','$password','$time','$ip','1')");
			mysql_query(" insert  into `{$dbPrefix}admin` (`admin_id`,`user_name`,`email`,`password`,`ec_salt`,`add_time`,`last_login`,`last_ip`,`nav_list`,`lang_type`,`agency_id`,`suppliers_id`,`todolist`,`role_id`) values ('1','$username','$email','$password',NULL,'$time',0,'$ip','','','0','0',NULL,'1')");
                    
            $message = '成功添加管理员<br />成功写入配置文件<br>安装完成．';
            $arr = array('n' => 999999, 'msg' => $message);
            echo json_encode($arr);
            exit;
        }

        include_once ("./templates/step4.php");
        exit();

    case '5':
    	$ip = get_client_ip();
    	$host = $_SERVER['HTTP_HOST'];
        $curent_version = file_get_contents(SITEDIR .'/Application/Admin/Conf/version.txt');
        $create_date = date("Ymdhis");
        $time = time();
        $mt_rand_str = $create_date.sp_random_string(6);
        $str_constant = "<?php".PHP_EOL."define('INSTALL_DATE',".$time.");".PHP_EOL."define('SERIALNUMBER','".$mt_rand_str."');";
        @file_put_contents(SITEDIR . '/Application/Common/Conf/constant.php', $str_constant);
        include_once ("./templates/step5.php");
        @touch('./install.lock');
        exit();
}

function testwrite($d) {
    $tfile = "_test.txt";
    $fp = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}

function sql_execute($sql, $tablepre) {
    $sqls = sql_split($sql, $tablepre);
    if (is_array($sqls)) {
        foreach ($sqls as $sql) {
            if (trim($sql) != '') {
                mysql_query($sql);
            }
        }
    } else {
        mysql_query($sqls);
    }
    return true;
}

function sql_split($sql, $tablepre) {

    if ($tablepre != "tp_")
    	$sql = str_replace("tp_", $tablepre, $sql);
          
    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);
    
    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}

function _dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

// 获取客户端IP地址
function get_client_ip() {
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

function dir_create($path, $mode = 0777) {
    if (is_dir($path))
        return TRUE;
    $ftp_enable = 0;
    $path = dir_path($path);
    $temp = explode('/', $path);
    $cur_dir = '';
    $max = count($temp) - 1;
    for ($i = 0; $i < $max; $i++) {
        $cur_dir .= $temp[$i] . '/';
        if (@is_dir($cur_dir))
            continue;
        @mkdir($cur_dir, 0777, true);
        @chmod($cur_dir, 0777);
    }
    return is_dir($path);
}

function dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function sp_password($pw, $pre){
	$decor = md5($pre);
	$mi = md5($pw);
	return substr($decor,0,12).$mi.substr($decor,-4,4);
}

function sp_random_string($len = 8) {
	$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
			"w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
			"H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
			"S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
			"3", "4", "5", "6", "7", "8", "9"
	);
	$charsLen = count($chars) - 1;
	shuffle($chars);    // 将数组打乱
	$output = "";
	for ($i = 0; $i < $len; $i++) {
		$output .= $chars[mt_rand(0, $charsLen)];
	}
	return $output;
}

?>