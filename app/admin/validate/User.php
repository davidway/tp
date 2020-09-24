<?php

namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 验证规则.
     */
    protected $rule = [

        'id|用户id'=>'>:0|number',
        'name|用户名称'=>'',
        'parent_id|邀请人id'=>'>:0|number',
        'parent_name|邀请人名称'=>'',
        'regist_start|注册开始时间'=>'date|<=:regist_end',
        'regist_end|注册结束时间'=>'date|>=:start',
        'identity|身份'=>'',
        'status|用户状态'=>''
    ];

    /**
     * 提示消息.
     */
    protected $message = [
        'id.>'                 => '请输入正确的 :attribute 值',
        'parent_id.>'           =>'请输入正确的 :attribute 值',
        'regist_start.<='             => '开始时间必须小于结束时间',
        'regist_start.date'           => '开始时间为时间格式',
        'regist_end.>='               => '结束时间必须大于开始时间',
        'regist_end.date'             => '结束时间为时间格式',
    ];

    /**
     * 字段描述.
     */
    protected $field = [
    ];

    /**
     * 验证场景.
     */
    protected $scene = [
        'search'  => ['id', 'name', 'parent_id', 'parent_name', 'regist_start', 'regist_end','identity','status']
    ];

}
