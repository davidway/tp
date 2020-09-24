<?php
namespace app\admin\model;
use think\Model;
class Course extends Model{
    function Student(){
        //  return $this->belongsTo('Admin','aid','id');
        //  return $this->belongsToMany('student','stu_cour','cour_id','stu_id');

    }

}

?>