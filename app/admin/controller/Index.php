<?php


namespace app\admin\controller;

use think\facade\View;

class Index extends Base
{
    public function index(){
        return View::fetch("index");
    }
    public function about(){
        return View::fetch("about");
    }
    public function openUserIndex(){
        return View::fetch("user/index");
    }
}