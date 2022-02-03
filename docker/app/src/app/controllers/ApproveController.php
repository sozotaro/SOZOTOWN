<?php
namespace App\Controller;

use App\Model\Approve;

class ApproveController{
    private $view;
    private $model;

    public function __construct()
    {
        $this->view = new \Smarty();
        $this->model = new Approve();
    }
}