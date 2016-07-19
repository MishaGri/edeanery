<?php

use Phalcon\Mvc\Model;

class Students extends Model
{
    private $Student_ID;
    private $Group_id;
    private $Name;

    public function getStudentID()
    {
        return $this->Student_ID;
    }

    public function setStudentID($Student_ID)
    {
        $this->Student_ID = $Student_ID;
    }

    public function getGroupId()
    {
        return $this->Group_id;
    }

    public function setGroupId($Group_id)
    {
        $this->Group_id = $Group_id;
    }

    public function getName()
    {
        return $this->Name;
    }

    public function setName($Name)
    {
        $this->Name = $Name;
    }
    public function initialize(){
        $this->hasOne("Group_id","Groups","ID_Group");
    }
}