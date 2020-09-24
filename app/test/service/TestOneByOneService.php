<?php


namespace app\admin\service;

use app\admin\model\User;

class TestOneByOneService{
    public $user;
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get()
    {
        //关联查询
        $user = $this->user->find(1);
        print_r($user->car);
        echo "车牌：{$user->car->plate_number}，用户名：{$user->name}";


    }

    public function find(){
        //方法一：使用关联预查询功能,有效提高性能。with*	用于关联预载入	字符串、数组

        $list = $this->user->with('car')->select();
        foreach($list as $user){
            echo "车牌：{$user->car->plate_number}，用户名：{$user->name}<br>";
        }


    }

    public function insert()
    {
        //关联新增
        $user = new User();
        $user->name='老黄';
        $user->sex='男';
        $user->age="24";
        $user->section='开发部';
        if ($user->save()){
            $car['brand']='奔驰';
            $car['plate_number']='A31949';
            //uid 不需要指定，自动添加
            $user->car()->save($car);
            return "用户：{$user->name}新增成功";
        }
    }

    public function update()
    {//关联更新
        $user = $this->user->find(1);
        $user->name = '小胜';
        if($user->save()){
            //更新关联数据
            $user->car->plate_number = '粤-A31937';
            $user->car->save();
        }
    }

    public function delete()
    {
        //关联删除
        $user = $this->user->find(1);
        if($user->delete()){
            //删除关联数据
            $user->car->delete();
            return "用户:{$user->name}删除了";
        }
    }

}