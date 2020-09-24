<?php


namespace app\admin\service;

use app\admin\model\Apartment;
use app\admin\model\Client;

class TestOneToManyService{
    public $apartment;
    public function __construct(Apartment $apartment)
    {
        $this->apartment = $apartment;
    }

    public function get()
    {
        //方法一：这个需要调用$apr->comm才查顾客表

        $apr = $this->apartment->find(1);
        $apr->comm;//注意这里不加括号
        //方法二：不用调用$apr->comm，预先查租客表
        $apr = $this->apartment(1,'comm');
        echo $apr;


    }

    public function find(){
        //方法一：使用关联预查询功能,有效提高性能。with*	用于关联预载入	字符串、数组

        //聚合查询，默认查询宿舍的租客人数'>='1的宿舍
        $apr = $this->apartment->has('comm','>=',1)->select();
        //查询宿舍的租客人数'>='3的宿舍
        $this->apartment->has('comm','>=','3')->where('apar_name','410')->select();
        echo($apr);


    }

    public function insert()
    {
        //新增，添加一位租客
        $array['apar_id']=1;
        $apr = $this->apartment->where($array)->find();

        $cli['cli_name'] = "小唐";
        $cli['cli_sex'] = '男';
        $cli['cli_phone']='1315***';
        $cli['cli_identity']='4487654334567610';
        $cli['cli_reservation']='没有';
        $apr->comm()->save($cli);


    }

    public function update()
    {//更新
        $idCondition['apar_id']=1;
        $apr = $this->apartment->where($idCondition)->find();
        $whereCondition['cli_name']='杨文';
        $comm = $apr->comm()->where($whereCondition)->find();
        $comm->cli_name='杨文';
        $comm->save();
        //或者通过update方法更新
        $apr = Apartment::get(1);
        $apr->comm()->where('cli_id',5)->update(['cli_name'=>'陈杨文']);
    }

    public function delete()
    {
        //删除id为13的租客
        $idCondition['apar_id']=1;
        $apr = $this->apartment->where($idCondition)->find();
        $array['cli_id']=3;
        $comm = $apr->comm()->where($array)->find();
        $comm->delete();
        //删除所有的关联数据

        $idCondition['apar_id']=2;
        $apr = $this->apartment->where($idCondition)->find();
        $apr->$comm()->delete();
    }

    public function insertAll()
    {
        $array['apar_id']=1;
        $apr = $this->apartment->where($array)->find();
        $cli = [
            ["cli_name"=>'阿K','cli_sex'=>'男','cli_phone'=>'1315***','cli_identity'=>'448***','cli_reservation'=>'没有'],
            ["cli_name"=>'小胡','cli_sex'=>'男','cli_phone'=>'1315***','cli_identity'=>'448***','cli_reservation'=>'没有']
        ];
        $apr->comm()->saveAll($cli);
    }

}