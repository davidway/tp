<?php

namespace app\admin\model;
use think\Model;

class User extends Model
{
    //定义关联方法
    public function car(){
        //hasOne('汽车表','汽车外键','用户主键',['模型别名定义'],'join类型');
        return $this->hasOne('car','uid','id');
    }
}