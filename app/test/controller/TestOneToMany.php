<?php


namespace app\admin\controller;
use app\admin\index\model\TestUsers;
use app\admin\service\TestOneToManyService;


class TestOneToMany extends Base
{
    public $testService;
    public function __construct(TestOneToManyService $testService)
    {
        parent::__construct();
        $this->testService = $testService;

    }

    public function index()
    {

    }
    public function detail(){

    }

    public function get(){
        $this->testService->get();
    }
    public function find(){
        $this->testService->find();
    }


    public function insert(){
        $this->testService->insert();
    }
    public function insertMany(){
        $this->testService->insertAll();
    }
    public function update(){
        $this->testService->update();
    }

    public function delete(){
        $this->testService->delete();
    }

}