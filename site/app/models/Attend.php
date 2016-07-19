<?php
use Phalcon\Mvc\Model;

class Attend extends Model
{
    private $Attend_id;
    private $Date;
    private $Subject_id;

    public function getAttendId()
    {
        return $this->Attend_id;
    }

    public function getDate()
    {
        return $this->Date;
    }

    public function setDate($Date)
    {
        $this->Date = $Date;
    }

    public function getSubjectId()
    {
        return $this->Subject_id;
    }

    public function setSubjectId($Subject_id)
    {
        $this->Subject_id = $Subject_id;
    }

    public function initialize()
    {
        $this->hasOne("Subject_id","Subjects","Subject_id");
    }
}