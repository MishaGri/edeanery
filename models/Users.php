<?php
use Phalcon\Mvc\Model;

class Users extends Model
{
    private $User_id;
    private $Login;
    private $Password;
    private $Name;
    private $Phone;
    private $Email;

    public function getUserId()
    {
        return $this->User_id;
    }

    public function setUserId($User_id)
    {
        $this->User_id = $User_id;
    }

    public function getLogin()
    {
        return $this->Login;
    }

    public function setLogin($Login)
    {
        $this->Login = $Login;
    }

    public function getPassword()
    {
        return $this->Password;
    }

    public function setPassword($Password)
    {
        $this->Password = $Password;
    }

    public function getName()
    {
        return $this->Name;
    }

    public function setName($Name)
    {
        $this->Name = $Name;
    }

    public function getPhone()
    {
        return $this->Phone;
    }

    public function setPhone($Phone)
    {
        $this->Phone = $Phone;
    }

    public function getEmail()
    {
        return $this->Email;
    }

    public function setEmail($Email)
    {
        $this->Email = $Email;
    }
}