<?php
namespace HappyCake\Core;

class Controller
{
    //объект View
    protected $view;

    // protected $model;

    function __construct()
    {
        $this->view = new View();
        //   $this->model = new Model();
    }

}