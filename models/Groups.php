<?php
use Phalcon\Mvc\Model;

class Groups extends Model
{
    private $ID_Group;
    private $Name_group;

    public function getIDGroup()
    {
        return $this->ID_Group;
    }

    public function setIDGroup($ID_Group)
    {
        $this->ID_Group = $ID_Group;
    }

    public function getNameGroup()
    {
        return $this->Name_group;
    }

    public function setNameGroup($Name_group)
    {
        $this->Name_group = $Name_group;
    }
}