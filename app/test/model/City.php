<?php

namespace app\admin\model;
use think\Model;

class City extends Model    //城市表
{
    public function area(){
        //belongsToMany('区域模型','中间表名','外键名','外键名');
        return $this->belongsToMany('Area','city_area','aid','cid');
    }
}