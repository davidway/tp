<?php


namespace app\admin\service;
use app\admin\model\User;
use think\facade\Db;

class UserService
{

    public function search(array $where)
    {
        unset($where);
        $where['isvalid']=true;
        $where['channel_id']=1;
        $user = new User();
       // $result = $user->where($where)->field('id,name,parent_id')->limit(0,10)->select();
        $result  = $user->alias('u1')->where($where)
            ->field('id,name,parent_id,(select name from user u2 where parent_id=u1.id) as parent_name')
            ->limit(0,10)->select();
       // $account = $user->user_account();

        return $result;
    }

    public function list()
    {
        $where['isvalid']=true;
        $where['channel_id']=1;
        $user = new User();
        // $result = $user->where($where)->field('id,name,parent_id')->limit(0,10)->select();
        $result  = $user->alias('u1')->where($where)
            ->field('id,name,parent_id,(select name from user u2 where parent_id=u1.id) as parent_name')
            ->limit(0,10)->select();
        // $account = $user->user_account();

        return $result;
    }

    public function forzen()
    {  $where['isvalid']=true;
        $where['channel_id']=1;
        $user = new User();
        // $result = $user->where($where)->field('id,name,parent_id')->limit(0,10)->select();
        $result  = $user->alias('u1')->where($where)
            ->field('id,name,parent_id,(select name from user u2 where parent_id=u1.id) as parent_name')
            ->limit(0,10)->select();
        // $account = $user->user_account();

        return $result;
    }

    public function add()
    {
        $user = new User();
        $user->name='weekendzhu';
        $user->parent_id=2;
        $user->save($user);
        return '成功';
    }
}