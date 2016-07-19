<?php
use Phalcon\Mvc\Model;

class Subjects extends Model
{
    private $Subject_id;
    private $User_id;
    private $Name;

    public function getUserId()
    {
        return $this->User_id;
    }

    public function setUserId($User_id)
    {
        $this->User_id = $User_id;
    }

    public function getName()
    {
        return $this->Name;
    }

    public function setName($Name)
    {
        $this->Name = $Name;
    }

    public function getSubjectId()
    {
        return $this->Subject_id;
    }

    public function setSubjectId($id)
    {
        $this->Subject_id = $id;
    }

}