<?php
namespace app\admin\controller;
use app\admin\library\Auth;
use app\BaseController;
use app\service\BaseService;
use app\service\setting\SettingService;
use itxq\apidoc\BootstrapApiDoc;
use think\App;
use think\facade\Lang;
use think\facade\Request;
use think\facade\View;


// 模板引擎
class Base extends BaseController
{

	/**
     * 无需登录的方法,同时也就不需要鉴权了.
     *
     * @var array
     */
    protected $noNeedLogin = ['apiDoc'];
    /**
     * 无需鉴权的方法,但需要登录.
     *
     * @var array
     */
    protected $noNeedRight = ['apiDoc'];
	protected $admin;
	protected $auth;
	protected $admin_id;
	protected $channel_id;
	protected $supply_id;
	protected $valid;

	public function __construct()
	{
		parent::__construct();




	}

	static protected function title($title = '')
	{
		$title = $title != '' ? $title : lang('gyl_system'); //供应链后台管理系统
		View::assign('title',$title);
	}

	static protected function nav_right($model_nav='',$active_nav = '')
	{
		$active_nav = $active_nav  ? $active_nav : lang('unkonwn'); //未知
		$model_nav  = $model_nav   ? $model_nav  : lang('unkonwn');
		View::assign('model_nav',$model_nav);
		View::assign('active_nav',$active_nav);
	}

	protected function checklogin()
	{
		$admin = $this->admin;
		if(!isset($admin['id']))
		{
			$this->error(lang('plese_again_login'),url('Login/index'));  //请重新登录！
		}
		$this->admin_id   = $admin['id'];
		$this->channel_id = $admin['channel_id'];

	}



    /**
     * 请求校验
     * @param        $class
     * @param string $scene
     * @throws \Exception
     */
    protected function checkVaild($class, string $scene = ''){
        if ($scene){
            $result = validate($class)->scene($scene)->check($this->request->param());
        }else{
            $result = validate($class)->check($this->request->param());
        }
        if (true !== $result){
            throw new \Exception($result);
        }
    }

    public function apiDoc(){
        $class = get_class($this);
        $config = [
            'class'         => [$class], // 要生成文档的类
            'filter_method' => ['__construct'], // 要过滤的方法名称
        ];
        $api = new BootstrapApiDoc($config);
        $doc = $api->getHtml();
        exit($doc);
    }


    /**
     * 添加导出定时任务
     * filename 导出的文件名
     * func_url 导出时候请求的方法
     * param_json 导出时候的条件
     * fields excel第一行标题
     */
    protected function add_excel_task($data = array())
    {
    	$base_service = new BaseService();
    	try{
    		validate("Base")->check($data);
    		$res = $base_service->add_excel_task($data);
    	}catch(\Exception $e){
    		$res['msg']  = $e->getMessage();
    		$res['code'] = $e->getCode() ?: 400;
    	}

    	return $res;
    }

    protected function logobeijing()
    {
        $channel_id = intval(Request::param('channel_id'));
        $setting    = new SettingService();
        $base_img   = $setting->baseSetting($channel_id);
        view::assign('base_img',$base_img);
    }


    public function apiDocAll(){
        $config = [
            'class'         => [
                auth\Auth::class,
                channel\Auth::class,
                channel\Channel::class,
                channel\Index::class,
                channel\Rulemenu::class,
                channel\WhiteList::class,
                Auth::class,
                Index::class,
                Login::class,
                Message::class,
                distr\Distr::class,
                order\Order::class
            ], // 要生成文档的类
            'filter_method' => ['__construct'], // 要过滤的方法名称
        ];
        $api = new BootstrapApiDoc($config);
        $doc = $api->getHtml();
        exit($doc);

    }

}
