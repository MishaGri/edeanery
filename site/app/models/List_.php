<?php

use Phalcon\Mvc\Model;

class List_ extends Model
{
    private $id;
    private $Attend_id;
    private $Student_id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAttendId()
    {
        return $this->Attend_id;
    }

    /**
     * @param mixed $Attend_id
     */
    public function setAttendId($Attend_id)
    {
        $this->Attend_id = $Attend_id;
    }

    /**
     * @return mixed
     */
    public function getStudentId()
    {
        return $this->Student_id;
    }

    /**
     * @param mixed $Student_id
     */
    public function setStudentId($Student_id)
    {
        $this->Student_id = $Student_id;
    }

    public function initialize()
    {
        $this->getSource('list');
        //$this->hasOne("Student_id","Students","Students_id");
        //$this->hasOne("Attend_id","Attend","Attend_id");
    }
}