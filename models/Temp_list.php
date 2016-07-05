<?php
use Phalcon\Mvc\Model;

class Temp_list extends Model
{
    private $temp_id;
    private $Attend_id;
    private $Student_id;

    public function getId()
    {
        return $this->temp_id;
    }

    public function getStudentId()
    {
        return $this->Student_id;
    }

    public function setStudentId($student_id)
    {
        $this->Student_id = $student_id;
    }

    public function getAttendId()
    {
        return $this->Attend_id;
    }

    public function setAttendId($attend_id)
    {
        $this->Attend_id = $attend_id;
    }

    public function initialize()
    {
        $this->getSource("list_temp");
    }
}