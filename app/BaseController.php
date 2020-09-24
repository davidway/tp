<?php
declare (strict_types = 1);

namespace app;

use think\App;

use think\Validate;
use think\facade\Cache; //缓存类
use think\facade\Config;
use think\exception\ValidateException;
use think\response\Redirect;
use think\exception\HttpResponseException;
use think\facade\Lang;
use think\facade\View; // 模板引擎
use oss\Ossfile;
/**
 * 控制器基础类
 */
class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 版本
     * @var array
     */
    protected $version = '';

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct()
    {

        $this->app     = App();
        $this->request = $this->app->request;
        $this->version = Config::get('app.version');
        $this->static  = Config::get('app.cdnurl').Config::get('app.static');
        $this->cdnurl  = Config::get('app.cdnurl');

        // 控制器初始化
        $this->initialize();
    }


    // 初始化
    protected function initialize()
    {
        //静态文件路径
        View::assign('_static',$this->static);

        //用于调试，防止静态缓存
        if($this->request->get('version')){
            $this->version = $this->request->get('version');
        }

        View::assign('_version',$this->version);

        //上传文件路径
        View::assign('_cdnurl',$this->cdnurl);
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }


    protected $staticHtmlDir  = ''; //静态模板生成目录
    protected $staticHtmlFile = ''; //静态文件

    /*
    * 判断是否有静态文件 有则跳转
    *
    */
    protected function beforebuildhtml($param=false)
    {
        if(is_array($param)) {
            $param = implode("_",$param);
        }
        $path                 = explode('app', app_path())[1];
        $app_now              = stripslashes($path);
        $this->staticHtmlDir  = 'html/'.$app_now.'/'.$this->request->controller().'/'.$this->request->action().'/';
        $this->staticHtmlFile = $this->staticHtmlDir.$this->request->action().($param?$param:'').'.html';
        $timeout              = Cache::store('redis')->get($this->staticHtmlFile);
        if(mkdirs($this->staticHtmlDir)) {
            if(file_exists($this->staticHtmlFile) && $timeout) { //静态文件存在
                header('Location: '.'/'.$this->staticHtmlFile);
                die();
            }
        }

    }

    /*
    * 创建静态文件
    *
    */
    protected function afterBuild($html,$timeout) {

        Cache::store('redis')->set($this->staticHtmlFile,'1',$timeout);
        if(!empty($this->staticHtmlFile) && !empty($html)) {
            if(file_exists($this->staticHtmlFile)) {
                unlink($this->staticHtmlFile);
            }
            file_put_contents($this->staticHtmlFile,$html);
        }
    }


    /*
    * 删除指定文件夹以及文件夹下的所有文件
    * $dir 文件夹 或者文件
    */
    protected function delhtml($dir = '')
    {
        $dir = root_path().'public/html/'.$dir;
        $res = deldir($dir);
        return $res;
    }

    /*
    * 删除指定文件夹以及文件夹下的所有文件
    * $filename  input 的name 值
    * $dir 保存的路径 默认 /public/应用名/$path/Ymd/文件
    */
    protected function upload($filename = '',$dir)
    {
        $path    = explode('app', app_path())[1];
        $app_now = stripslashes($path);
        $app_now = trim($app_now,'/');
        $path    = $app_now.'/'.$dir;
        $file    = $this->request->file($filename);
        if (empty($file)) {
            return array('code' => 400,'msg'=>'没有上传的文件！');
        }
        $upload = Config::get('upload'); //获取上传配置信息
        $size   = (int) $upload['maxsize'] * 1024 * 1024;

        $fileInfo['name']     = $file->getOriginalName(); //上传文件名
        $fileInfo['type']     = $file->getOriginalMime(); //上传文件类型信息
        $fileInfo['tmp_name'] = $file->getPathname();
        $fileInfo['size']     = $file->getSize();

        if($fileInfo['size'] > $size){
            return array('code' => 400,'msg'=>'文件超过配置的最大值'.$size.'M');
        }

        $suffix      = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix      = $suffix && preg_match('/^[a-zA-Z0-9]+$/', $suffix) ? $suffix : 'file';
        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr     = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            return array('code' => 400,'msg'=> '该文件类型不可上传');
        }
        //验证文件后缀
        if ($upload['mimetype'] !== '*' && (! in_array($suffix, $mimetypeArr) || (stripos($typeArr[0].'/', $upload['mimetype']) !== false && (! in_array($fileInfo['type'],$mimetypeArr) && ! in_array($typeArr[0].'/*', $mimetypeArr))))) {
            return array('code' => 400,'msg'=> '该文件类型不可上传');
        }
        //验证是否为图片文件
        $imagewidth = $imageheight = 0;
        if (in_array($typeArr[1],explode(',',$fileInfo['type']))) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (! $imgInfo || ! isset($imgInfo[0]) || ! isset($imgInfo[1])) {
                return array('code' => 400,'msg'=> '不是图片文件');
            }
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }

        //上传图片
        $savename = false;
        try {
            if($upload['onoffcnd'] == false){
                $savename = upload_file($file, 'public', $path);
                $savename = $savename; //保存用；存数据库不需要拼接Config::get('app.app_host')
                $showname = trim(Config::get('app.cdnurl'),'/').$savename; //展示用；
            }else{ // 这里调用cnd 上传 使用cnd 接口，后期补充
                $savename = Ossfile::instance()->upload($file,$path);
            }
        } catch (\Exception $e) {
            _file_put_contents('upload_err.log',$e->getMessage());
            return  array('code' => 400,'msg'=> '上传失败');
        }

        return  array('code' => 200,'msg'=> '上传成功','data' =>array('img'=>$savename,'show_img'=>$showname));

    }


    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function success($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html'){
            $response = view($this->app->config->get('app.dispatch_success_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     * @return void
     */
    protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html'){
            $response = view($this->app->config->get('app.dispatch_error_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }
        throw new HttpResponseException($response);
    }

    /**
     * URL重定向  自带重定向无效
     * @access protected
     * @param  string         $url 跳转的URL表达式
     * @param  array|integer  $params 其它URL参数
     * @param  integer        $code http code
     * @param  array          $with 隐式传参
     * @return void
     */
    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        $response = Response::create($url, 'redirect');

        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }

        $response->code($code)->params($params)->with($with);

        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return $this->request->isJson() || $this->request->isAjax() ? 'json' : 'html';
    }

    /**
     * 加载语言文件.
     *
     * @param string $name
     */
    protected function loadlang($type = 'zh-cn')
    {
        $path    = explode('app', app_path())[1];
        $app_now = stripslashes($path);
        $app_now = trim($app_now,'/');
        $name    = $app_now.'_'.strtolower($this->request->controller());
        Lang::load(app()->getAppPath().'/lang/'.$type.'/'.$name.'.php');

    }
}
