<?php
namespace app\admin\model;
use think\Model;

class Student extends Model{

    public   function Course(){
        //cour_id是关联中间表的关联course表的外键id，而stu_id也是中间表关联学生表的外键id
        //两个id都是中间表关联其他表的外键id
        //这个大家应该一看就知道都是中间表的id，因为在`pwn_student` 学生表和`pwn_Course`课程表里没有这两个字段。
        return $this->belongsToMany('course','stu_cour','cour_id','stu_id');
    }
}

?>