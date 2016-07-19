<?php

use Phalcon\Mvc\Controller;
use Phalcon\Session\Adapter\Files as Session;

class IndexController extends Controller
{

    public function indexAction()
    {
        $this->assets
            //->addCss("css/main.css");
            ->addCss("css/bootstrap.min.css");
        $this->tag->setTitle("Главная страница");
        /*if(!empty($_SESSION)&&$this->session->has('login')/*isset($_SESSION['login']))
        {
            echo "<h1>Привет!</h1>";
            echo("<h1>Привет,  $this->session->get('login')</h1>");
        }
        else
        {
            echo("Пока вы не авторизованы");
        }*/
    }
}
