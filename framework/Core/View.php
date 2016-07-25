<?php
namespace HappyCake\Core;

class View
{
    public function render($content, $template = 'default.phtml', $data = null)
    {
        $logged = false;
        $userAvatar = '';
        if (is_array($data)) {
            extract($data);
        }


        var_dump($data);
        ob_start();
        require_once 'Application/Views/' . $content;
        $page = ob_get_clean();
        require_once 'Application/Layouts/' . $template;
    }

}