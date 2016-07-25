<?php
namespace HappyCake\Core;

use Application\Controllers\ControllerError;
use HappyCake\Http\Request;


class Router
{
    private $controller;

    private $action;

    private $params;


    public function start()
    {
        $request = Request::createFromGlobals();

        $this->controller = $request->getController() ?: DEFAULT_CONTROLLER;
        $this->action = $request->getAction() ?: DEFAULT_ACTION;
        $this->params = $request->getParams();


        $this->controller = "Controller" . ucfirst($this->controller);
        $this->action = "action" . ucfirst($this->action); // Получили имя действия

        try {
            $this->controller = PREFIX . $this->controller;

            if (!class_exists($this->controller)) {
                throw new \Exception("Controller $this->controller not found");
            }

            $controller = new $this->controller;
            if (method_exists($controller, $this->action)) {
                $controller->{$this->action}($this->params);
            } else {
                throw new \Exception();
            }
        } catch (\Exception $e) {
            $controller = new ControllerError();
            $controller->actionIndex();
        }
    }


}


