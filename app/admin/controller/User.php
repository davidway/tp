<?php


namespace app\admin\controller;
use app\admin\service\UserService;
use app\service\channel\LoginService;
use think\facade\View;

class User extends  Base
{

    public function __construct()
    {
        parent::__construct();

        $this->valid = \app\admin\validate\User::class;
    }

    public function index(){
        return View::fetch("index");
    }
    public function add_index(){
        return View::fetch("add");
    }

    public function search(){
        try {
            $userService = new UserService();
            // $this->checkVaild($this->valid, 'login');
            //$data = $this->request->only(['id', 'name','parent_id', 'parent_name','regist_start','regist_end','identity','status']);
            $data=[];
            $data['id']=1;
            $data['name']='weekend';
            $data['parent_id']=2;
            $data['parent_name']='ah';
           // $data['regist_start']='2020-09-24';
            //$data['regist_end']='2020-09-24';
            $data['identity']=1;
            $data['status']=0;


            $result = $userService->search($data);
            return _success('成功', $result);
        } catch (\Exception $e) { //错误消息 $e->getMessage()
            return _error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
    public function list(){
        try {
            $userService = new UserService();


            $result = $userService->list();
            return _success('成功', $result);
        } catch (\Exception $e) { //错误消息 $e->getMessage()
            return _error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
    public function frozen(){
        try {
            $userService = new UserService();


            $result = $userService->forzen();
            return _success('成功', $result);
        } catch (\Exception $e) { //错误消息 $e->getMessage()
            return _error($e->getMessage(), $e->getCode() ?: 400);
        }

    }

    public function add(){
        try {
            $userService = new UserService();


            $result = $userService->add();
            return _success('成功', $result);
        } catch (\Exception $e) { //错误消息 $e->getMessage()
            return _error($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}