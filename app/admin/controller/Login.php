<?php


namespace app\admin\controller;



use think\facade\View;

class Login extends Base
{
    public function __construct() {
        parent::__construct();
        $this->valid = \app\admin\validate\Login::class;
    }

    public function index(){
            return View::fetch("index");
        }
        public function login(){
            try {
                $this->checkVaild($this->valid, 'login');
                $country_code = $this->request->param('country_code', '+86');
                $phone        = $this->request->param('phone');
                $password     = $this->request->param('password');
                $captcha      = $this->request->param('captcha');
                $type         = $this->request->param('type', 'admin');
                $cate         = $this->request->param('cate', 'account'); //account-账号密码 sms-短信验证码

                $url = url('index/index');

                return _success('登录成功', ['url' => (string)$url]);
            } catch (\Exception $e) { //错误消息 $e->getMessage()
                return _error($e->getMessage(), $e->getCode() ?: 400);
            }
        }
}