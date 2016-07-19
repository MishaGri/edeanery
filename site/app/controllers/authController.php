<?php

use Phalcon\Mvc\Controller;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Http\Response;
class authController extends Controller
{
    public function indexAction()
    {
        $this->tag->setTitle("Авторизация");
    }
    public function checkAction()
    {
        try
        {
            if($this->request->isPost())
            {
                $login=$this->request->getPost('Login');
                $password=md5($this->request->getPost('Password'));
                $user=Users::findFirst(array(
                    "Login = :login: AND Password = :password:",
                    'bind' => array(
                        'login'    => $login,
                        'password' => $password
                    )
                ));

                if($user!=false)
                {
                    $this->session->set("user",$user->getName());
                    $this->session->set("pass",$user->getPassword());
                    $response = new Response();
                    return $response->redirect();
                }
                else
                {
                    echo "<script>
                            alert(\"Не верный логин или пароль\");
                          </script>";
                    $response = new Response();
                    return $response->redirect();
                }

            }
        }
        catch (\Exception $e)
        {
            echo get_class($e), ": ", $e->getMessage(), "\n";
            echo "<br/>";
            echo " File=", $e->getFile(), "\n";
            echo "<br/>";
            echo " Line=", $e->getLine(), "\n";
            echo "<br/>";
            echo $e->getTraceAsString();
        }

    }
}