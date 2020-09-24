<?php


namespace app\admin\service;

use app\admin\model\Apartment;
use app\admin\model\Client;
use app\admin\model\Area;
use app\admin\model\City;
use think\facade\Db;

class TestManyToManyService{
    public $city;
    public $area;
    public function __construct(City $city,Area $area)
    {
        $this->city = $city;
        $this->area = $area;
    }

    public function get()
    {
        $idContion['c_id']=1;
        $city = $this->city->where($idContion)->find();

        foreach($city->area() as $role){
            // 获取城市id为1的所有区域名称
            dump($role->a_name);
        }

       echo '';



    }

    public function find(){
        //方法一：使用关联预查询功能,有效提高性能。with*	用于关联预载入	字符串、数组

        //聚合查询，默认查询宿舍的租客人数'>='1的宿舍
        $apr = $this->apartment->has('comm','>=',2)->select();
        //查询宿舍的租客人数'>='3的宿舍
        $this->apartment->has('comm','>=','3')->where('apar_name','410')->select();
        echo($apr);


    }

    public function insert()
    {
        //关联单条新增
        $idContion['c_id']=1;
        $city = $this->city->where($idContion)->find();
        //增加关联数据 会自动写入中间表数据
        $city->area()->save(['a_name'=>'珠三角地区']);
        //批量新增
//        $city->area()->saveAll([
//            ['a_name'=>'一线城市'],
//            ['a_name'=>'羊城'],
//        ]);


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