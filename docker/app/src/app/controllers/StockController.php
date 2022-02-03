<?php

namespace App\Controller;

use App\Model\Stock;

class StockController
{
    private $view;
    private $model;

    public function __construct()
    {
        $this->view = new \Smarty();
        $this->model = new Stock();
    }

}