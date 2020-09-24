<?php

namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    /**
     * 验证规则.
     */
    protected $rule = [
        'country_code|国家区号' => 'regex:\+\d{1,3}',
        'phone|手机号码'        => 'require|mobile',
        'password|密码'       => 'require',
        'captcha|验证码'       => 'regex:\d{4}',
        'type|登录类型'         => 'in:admin,channel',
        'cate|登录方式'         => 'in:account,sms',
    ];

    /**
     * 提示消息.
     */
    protected $message = [
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
        'login'  => ['country_code', 'phone', 'password', 'captcha', 'type', 'cate']
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'username' => '用户名',
            'nickname' => '昵称',
            'password' => '密码',
            'email'    => '邮箱',
        ];
        parent::__construct($rules, $message, $field);
    }
}
