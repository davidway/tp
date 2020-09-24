<?php

use think\facade\Config;
use think\facade\Cache; 
use think\facade\Db;
use app\model\DistrSetting;
// 应用公共文件

// 在指定字符串中查找指定的数组中所有子串  $returnvalue true表示返回数组中匹配到的值   默认false
function dstrpos($string, $arr, $returnvalue = false) {
    if (empty ($string)) {
        return false;
    }
    foreach (( array )$arr as $v) {
        if (stripos($string, $v) !== false) {
            $return = $returnvalue ? $v : true;
            return $return;
        }
    }
    return false;
}

/*
 * 递归创建目录
 * @param string $dir 文件目录路径
 * @return boolean 创建结果
*/
function mkdirs($dir)
{
    if(!is_dir($dir))
    {
        if(!mkdirs(dirname($dir))){
            return false;
        }
        if(!mkdir($dir,0777)){
            return false;
        }
    }
    return true;
}

/*
* 删除指定文件夹以及文件夹下的所有文件
* $dir 文件夹 或者文件
*/
function deldir($dir = '') 
{
    if($dir == '') return false;
    if(is_dir($dir)) //删除文件夹和里面的文件
    { 
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
          if($file!="." && $file!="..") {
             $fullpath=$dir."/".$file;
             if(!is_dir($fullpath)) {
                unlink($fullpath);
             } else {
                deldir($fullpath);
             }
          }
        }
        closedir($dh);//删除当前文件夹：
        if(rmdir($dir)) {
          return true;
        } else {
          return false;
        }
    }
    //如果是文件直接删除文件
    if(file_exists($dir)){
        unlink($dir);
    }
    
    return true;
}


// 验证Email是否合法
if (!function_exists('is_email')) {//防止redeclare
    function is_email($str) {
        return strlen($str) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $str);
    }
}

// 验证手机号码
if (!function_exists('is_mobile')) {//防止redeclare
    function is_mobile($str) {
        return preg_match("/^1[3-9]{1}[0-9]{9}$/", $str) ? true : false;
    }
}

// 对象转数组
if (!function_exists('object_to_array')) {//防止redeclare
    function object_to_array($d) {
        if (is_object($d)) {
            $d = get_object_vars($d); //将第一层对象转换为数组
        }

        if (is_array($d)) {
            return array_map(__FUNCTION__, $d); //如果是数组使用array_map递归调用自身处理数组元素
        } else {
            return $d;
        }
    }
}

// 数组转对象
if (!function_exists('array_to_object')) {//防止redeclare
    function array_to_object($d) {
        if (is_array($d)) {
            return ( object )array_map(__FUNCTION__, $d);
        } else {
            return $d;
        }
    }
}


// 获取ip地址
if (!function_exists('getIP')) {//防止redeclare
    function getIP($ipstr = '') {
        if ($ipstr) {
            //user_ip|s:9:"127.0.0.1"
            preg_match('/(?:\d{1,3}\.){3}\d{1,3}/is', $ipstr, $arr);
            return $arr [0];
        }
        static $realip = null;
        if ($realip !== null) {
            return $realip;
        }
        //REMOTE_ADDR 是你的客户端跟你的服务器握手时候的IP。如果使用了“匿名代理”，REMOTE_ADDR将显示代理服务器的IP。
        $realip = $_SERVER['REMOTE_ADDR'];
        //使用云加速获取真实ip
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && filter_var($_SERVER ['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
            $realip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } //使用cdn后获取真实ip
        elseif (isset($_SERVER['ALI-CDN-REAL-IP']) && filter_var($_SERVER ['ALI-CDN-REAL-IP'], FILTER_VALIDATE_IP)) {
            $realip = $_SERVER['ALI-CDN-REAL-IP'];
            //使用nginx代理模式下,获取客户端真实IP
        } elseif (isset ($_SERVER ['HTTP_X_REAL_IP']) && filter_var($_SERVER ['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
            $realip = $_SERVER ['HTTP_X_REAL_IP'];
        } //HTTP_CLIENT_IP 是代理服务器发送的HTTP头。如果是“超级匿名代理”，则返回none值（有可能存在，也可以伪造）
        elseif (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER ['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
            //HTTP的请求端真实的IP，只有在通过了HTTP 代理(比如APACHE代理)或者负载均衡服务器时才会添加该项 （有可能存在，也可以伪造）
        }
        // elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        //     foreach ($matches[0] AS $xip) {
        //         if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
        //             $realip = $xip;
        //             break;
        //         }
        //     }
        // }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $realip = $xip;
                    break;
                }
            }
        }
        
        //验证ip地址合法性
        $realip = filter_var($realip, FILTER_VALIDATE_IP) ? $realip : 'unknown';
        return $realip;
    }
}


/*
** 写入日志文件
** $name文件名 $data 数据
 */
function _file_put_contents($name='',$data)
{
    //$data = stripslashes($data);//stripslashes的参数只能是string，参数是数组会炸
    if($name == '') return false;
    $path = root_path().'public/log/'.date('Ymd').'/';
    $res  = mkdirs($path);
    if($res)
    {
        file_put_contents($path.$name,date('Y-m-d H:i:s')."\r".print_r($data,true)."\r\n".PHP_EOL,FILE_APPEND);
        return true;
    }    
    return false;
}


if (! function_exists('upload_file')) 
{
    /**
     * 上传文件.
     *
     * @param  string  $file  上传的文件
     * @param  string  $name  上传的位置
     * @param  string  $path  上传的文件夹
     * @param  string  $validate  规则验证
     *
     * @return string|bool
     * @author niu
     */
    function upload_file($file = null, $name = 'local', $path = '', $validate = '')
    {
        //文件
        if (! $file) {
            return false;
        }
        //上传配置
        $config_name = 'filesystem.disks.'.$name;
        $filesystem = config($config_name);
        if (! $filesystem) {
            return false;
        }
        //上传文件
        if ($validate) {
            validate(['file' => $validate])->check(['file' => $file]);
        }
        $savename = \think\facade\Filesystem::disk($name)->putFile($path, $file, function ($file) {
            //重命名
            return date('Ymd').'/'.md5((string) microtime(true));
        });
        if (isset($filesystem['url'])) {
            $savename = $filesystem['url'].$savename;
        }

        return $savename;
    }
}


/**
 * 发布消息订阅
 * $channel 订阅的事件
 * $da 发送的数据
 * $times 次数
 */
function redis_publish($channel,$da,$times=0)
{
    $handler     = Cache::store('redis')->handler();
    $res         = $handler->publish($channel,serialize($da));
    $config_name = 'app.php_cli';
    $phpcli      = config($config_name);
    if($times == 3 && $res == 0){
        $da['msg'] = "redis 监听类服务启动失败！";
        _file_put_contents("redis_publish.log",$da);
        return false;
    }

    if($res == 0 && $times < 3)
    {
        //启动监听服务
        $cli_path = root_path()."public";
		switch($channel){
				case 'email_send': // 邮件发送通道
					system("cd $cli_path && nohup $phpcli index.php task/Redislisten/send_mail >>$cli_path/log/server/sms_send.log 2>&1 &"); //nohup 只有再liunx服务器才有的命令
					break;
				case 'sms_send': // 短信发送通道
					system("cd $cli_path && nohup $phpcli index.php task/Redislisten/send_sms >>$cli_path/log/server/sms_send.log 2>&1 &"); //nohup 只有再liunx服务器才有的命令
					break;
                case 'bill_order_detail': // 创建账单结算明细
                    // system("cd $cli_path && nohup $phpcli index.php task/Redislisten/send_sms  >>output.txt &"); //nohup 只有再liunx服务器才有的命令
                    system("cd $cli_path && $phpcli index.php task/Redislisten/bill_order_detail >>$cli_path/log/server/bill_order_detail.log 2>&1 &"); 
                    break;
				case 'exit': //退出监听
					echo '退出成功！';
					die();
					break;
				default:
					# code...
					break;
		}
        //system("nohup cd $cli_path & $phpcli index.php task/Redislisten/send_mail  >>output.txt &"); //nohup 只有再liunx服务器才有的命令
        sleep(0.5);
        //再次调用加入订阅
        redis_publish($channel,$da,$times+1);
    }

    return true;
}



if (! function_exists('_success'))
{
    function _success($msg, $data = null, $return_array = false){
        $result = [
            'code' => 200,
            'msg' => $msg
        ];
        if ($data != null){
            $result['data'] = $data;
        }
        if ($return_array){
            return $result;
        }else{
            return json($result);
        }
    }
}

if (!function_exists('_error'))
{
    function _error($msg, int $code = 400, $data = null, $return_array = false){
        $result = [
            'code' => $code,
            'msg' => $msg
        ];
        if ($data != null){
            $result['data'] = $data;
        }
        if ($return_array){
            return $result;
        }else{
            return json($result);
        }
    }
}



/**根据hash获取table**/
if (!function_exists('hash_tablename'))
{
    function hash_tablename($table,$id)
    {
        if(!$table)
        {
            $showmsg = '请传入表名';
            exit($showmsg);
        }

        if(!$id)
        {
            $showmsg = '请传入id值';
            exit($showmsg);
        }

        if(is_array($id))
        {
            for($i = 0; $i<count($id); $i++)
            {
                $table_arr[$i] = $table.'_'.getHash($id[$i]);

                is_table($table.'_'.$num,'mysql','产品id参数有误');
            }
        }
        else
        {
            $table_arr = $table.'_'.getHash($id);
        }
        return $table_arr;

    }
}

//获取hash分表，10张表
if (!function_exists('getHash'))
{
    function getHash($string, $tab_count=10)  
    { 
            $unsign = sprintf('%u', crc32($string));  
            if ($unsign > 2147483647)  // sprintf u for 64 & 32 bit  
            {  
                $unsign -= 4294967296;  
            }  
            return abs($unsign) % $tab_count;  
    }
}

/**根据订单号获取表**/
if (!function_exists('batchcode_tablename'))
{
    function batch_tablename($table,$batch)
    {
        if(!$table)
        {
            $showmsg = '请传入表名';
            exit($showmsg);
        }

        if(!$batch)
        {
            $showmsg = '请传入订单号';
            exit($showmsg);
        }
        $time       = substr($batch,0,10);//输出el
        $table_name = $table.'_'.date("Ym",$time);

        is_table($table_name,'mysql','订单号有误');
        
        return $table_name;

    }
}

/**根据sku_no获取sku表**/
if (!function_exists('skuno_tablename'))
{
    function skuno_tablename($table,$sku_no)
    {
        if(!$table)
        {
            $showmsg = '请传入表名';
            exit($showmsg);
        }

        if(count($sku_no) < 1)
        {
            $showmsg = '请传入sku_no参数';
            exit($showmsg);
        }

        $table_name = [];
        foreach($sku_no as $k => $v)
        {
            $str = explode("sku_",$v);
            if(!isset($str[1])){
                continue;
            }

            $num          = substr($str[1],0,1);

            $table_name[] = $table.'_'.$num;

            is_table($table.'_'.$num,'prod','sku_no参数有误');
        }

        $un_table = array_unique($table_name);

        return $un_table;

    }
}


function is_table($table_name,$connect='mysql',$msg='')
{
    $sql  = "SELECT TABLE_NAME FROM information_schema. TABLES T  WHERE T.TABLE_NAME REGEXP '^{$table_name}' AND T.TABLE_SCHEMA = (select database())";
    $data =  Db::connect($connect)->query($sql);
    
    if($data == false){
        throw new \Exception($msg);
    }
}

/**
 * [扣减/增加产品库存]
 * @param  [type] $sku_no [sku唯一编号]
 * @param  [type] $channel_id [渠道商id]
 * @param  [type] $nums [扣减/增加数量]
 * @param  [type] $pro_id [产品id]
 *  @param  [remark] $type [类型1-扣减 2-增加]
 */
if (!function_exists('dec_inc_stock'))
{   
    function dec_inc_stock($table,$da,$type=1)  
    { 
        extract($da);
        if(!isset($sku_no) || !isset($channel_id) || !isset($nums) || !isset($pro_id))
        {
            throw new \Exception('缺少参数');
        }

        if($type == 1)
        {
            $res = Db::connect('prod')->table($table)->where("sku_no ='{$sku_no}' and pro_id=$pro_id")->dec('stock',$nums)->update();
            if($res <= 0){
                throw new \Exception("扣减库存失败,查不到产品数据");
            }
            Db::connect('prod')->table('products')->where("id=$pro_id")->dec('stock',$nums)->update();
        }
        else
        {
            $res = Db::connect('prod')->table($table)->where("sku_no ='{$sku_no}' and pro_id=$pro_id")->inc('stock',$nums)->update();
            if($res <= 0){
                throw new \Exception("增加库存失败,查不到产品数据");
            }
            Db::connect('prod')->table('products')->where("id=$pro_id")->inc('stock',$nums)->update();
        }
        
    }
}

/**
 * [预占/释放产品库存]
 * @param  [type] $sku_no [sku唯一编号]
 * @param  [type] $channel_id [渠道商id]
 * @param  [type] $nums [扣减/增加数量]
 * @param  [type] $distr_id [商家id]
 * @param  [type] $status [1-预占库存 2-释放库存]
 * @param  [type] $code [唯一编码]
 */
if (!function_exists('add_stock_reserve'))
{   
    function add_stock_reserve($da)  
    { 
        extract($da);
        if(!isset($sku_no) || !isset($channel_id) || !isset($nums) || !isset($distr_id) || !isset($code) || !isset($status))
        {
            throw new \Exception('缺少参数');
        }
        $da['createtime'] = date('Y-m-d H:i:s');
        $ym               = date("Ym");
        $table            = 'product_stock_reserve_'.$ym;
        Db::connect('prod')->table($table)->insert($da);
    }
}

/**
 * [扣减增加产品库存日志]
 * @param  [type] $sku_no [sku唯一编号]
 * @param  [type] $channel_id [渠道商id]
 * @param  [type] $nums [扣减/增加数量]
 * @param  [type] $pro_id [产品id]
 * @param  [type] $before_num [扣减前库存]
 * @param  [type] $after_num [扣减后库存]
 * @param  [type] $remark [说明]
 * @param  [remark] $type [类型1-扣减 2-增加]
 */
if (!function_exists('dec_inc_stock_log'))
{   
    function dec_inc_stock_log($da)  
    { 
        extract($da);
        if(!isset($sku_no) || !isset($channel_id) || !isset($nums) || !isset($pro_id) || !isset($remark) || !isset($before_num) || !isset($after_num))
        {
            throw new \Exception('缺少参数');
        }

        //插入日志
        $da['createtime'] = date('Y-m-d H:i:s');
        $ym               = date("Ym");
        $ta_log           = 'product_stock_log_'.$ym;
        Db::connect('prod')->table($ta_log)->insert($da);
    }
}


/**
 * [扣减增加分销商余额]
 * @param  [type] $distr_id [分销商id]
 * @param  [type] $channel_id [渠道商id]
 * @param  [type] $money [扣减/增加的金额]
 * @param  [remark] $type [类型pay-货款扣除 recharge-货款充值 sale-订单完成增加销售额]
 */
if (!function_exists('dec_inc_distr'))
{   
    function dec_inc_distr($da,$type='pay')  
    { 
        extract($da);
        if(!isset($distr_id) || !isset($channel_id) || !isset($money))
        {
            throw new \Exception('分销商余额扣减缺少参数');
        }
        $where['channel_id'] = $channel_id;
        $where['distr_id']   = $distr_id;

        if($type == 'pay')
        {
            Db::table('distr_account')->where($where)->dec('account',$money)->update();
        }
        else if($type == 'sale'){
            Db::table('distr_account')->where($where)->inc('sum_sales_price',$money)->update();
        }
        else if($type == 'recharge')
        {
            Db::table('distr_account')->where($where)->inc('account',$money)->update();
        }
        
    }
}


/**
 * [扣减增加分销商余额日志]
* @param  [type] $distr_id [分销商id]
 * @param  [type] $channel_id [渠道商id]
 * @param  [type] $money [扣减/增加的金额]
 * @param  [type] $batchcode [订单号]
 * @param  [type] $before_account [扣减前余额]
 * @param  [type] $after_account [扣减后余额]
 * @param  [type] $remark [说明]
 * @param  [remark] $type [类型pay-货款扣除 recharge-货款充值]
 */
if (!function_exists('dec_inc_distr_log'))
{   
    function dec_inc_distr_log($da)  
    { 
        extract($da);
        if(!isset($distr_id) || !isset($channel_id) || !isset($type) || !isset($money) || !isset($batchcode) || !isset($remark) || !isset($before_account) || !isset($after_account))
        {
            throw new \Exception('分销商余额日志缺少参数');
        }

        //插入日志
        $ym               = date("Ym");
        $ta_log           = 'distr_account_log_'.$ym;
        Db::table($ta_log)->insert($da);
    }
}


/**
 * [创建订单号方法]
 * @param  [type] $distr_id [分销商id]
 */
if (!function_exists('create_batchcode'))
{   
    function create_batchcode($distr_id = 1000)  
    { 
        $batchcode = time().$distr_id.rand(100000,999999);
        return $batchcode;
    }
}


/**
 * [创建时间戳相同的订单号]
 * @param  [type] $distr_id [分销商id]
 */
if (!function_exists('create_batchcode2'))
{
    function create_batchcode2($batchcode,$distr_id = 1000)  
    { 
        $time      = substr($batchcode,0,10);//输出el
        $batchcode = $time .$distr_id.rand(100000,999999);
        return $batchcode;
    }
}


/**
 * [获取接口签名]
 * @param  [type] $distr_id [分销商id]
 */
if (!function_exists('get_sign'))
{   
    function get_sign($distr_id,$timestamp)  
    { 
        $distr = new DistrSetting();
        $info  = $distr->info($distr_id); 
        $sign  = md5($info['appid'].$info['secret'].$timestamp);
        $info['sign'] = $sign;    
        return $info;
    }
}


/**
 * [将三维数组转为二维数组]
 */
if (!function_exists('arr_for_two'))
{   
    function arr_for_two($res)  
    { 
        if(count($res) > 1)
        {
            $new_arr = [];
            foreach ($res as $key =>$v)
            {
                foreach ($v as $a => $b) 
                {
                    $new_arr[]=$b;
                }
            }
            $res = $new_arr;
        }
        else
        {
            $res = $res[0];
        }

        return $res;
    }
}

/**
 * [向下舍入为最接近的小数]
 * @param  [type] $data     [数据]
 * @param  [type] $decimals [保留位数]
 * @return [type]           [description]
 */
if (!function_exists('floor_decimals'))
{ 
    function floor_decimals($data, $decimals)
    {
        $data = bcadd($data, 0, $decimals);
        return $data;
    }
}

//需要解序列号的redis，要有到，否则接序列化，可能会出错
if (!function_exists('redis_get'))
{ 
    function redis_get($key)
    {
       $res = Cache::store('redis')->get($key);
       if($res == 1)
       {
            return false;
       }
       else
       {
            $res = unserialize($res);
            return $res;
       }

    }
}


//返回图片路径，带域名地址
if (!function_exists('domain_url'))
{ 
    function domain_url($img,$type = 1)
    {   
        if($img == '')
        {
            return '';
        }
        if($type == 1)
        {
            $img_arr = explode(',',$img);
        }
        else
        {
            $img_arr = json_decode($img,true);

        }
       
        $cdn  = config('app.cdnurl');
        $host = config('app.app_host');
        foreach ($img_arr as $k => &$v) 
        {
            if(strpos($v,'http')  !== false)
            {
                
            }
            else if($cdn != '')
            {
                $v = $cdn.$v;
            }
            else
            {
                $v = $host.$v;
            }
        }
        $imgs = implode(',',$img_arr);
        return $imgs;
    }
}

//返回唯一值
if (!function_exists('_uniqid'))
{
    function _uniqid()
    {
        $res = md5(uniqid(microtime() . mt_rand()));
        return substr($res, 8, 16);
    }
}