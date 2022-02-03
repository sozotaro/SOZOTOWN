<?php

namespace App\Controller;

use App\Model\Mail;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

define("SERVER_URI", $_ENV['SERVER_URI']);


class MailController
{
    private $view;
    private $model;

    public function __construct()
    {
        $this->view = new \Smarty();
        $this->model = new Mail();
    }

}