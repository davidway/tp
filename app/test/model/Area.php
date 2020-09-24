<?php

namespace app\admin\model;
use think\Model;

class Area  extends Model   //全国区域表
{
    public function city(){
        //belongsToMany('城市模型','中间表名','外键名','外键名');
        return $this->belongsToMany("Area",'city_area','aid','cid');
    }
}