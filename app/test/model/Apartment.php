<?php


namespace app\admin\model;


use think\Model;

class Apartment extends Model
{
    public function comm(){
        return $this->hasMany('client','aid','apar_id');
    }
}