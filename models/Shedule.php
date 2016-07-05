<?php
use Phalcon\Mvc\Model;

class Shedule extends Model
{
    private $Subject_id;
    private $Number;
    private $Below_above;
    private $Weekday;

    public function getSubjectId()
    {
        return $this->Subject_id;
    }

    public function setSubjectId($Subject_id)
    {
        if (is_string($Subject_id) && count_chars($Subject_id)<=50)
        {
            $this->Subject_id = $Subject_id;
        }

    }

    public function getNumber()
    {
        return $this->Number;
    }

    public function setNumber($Number)
    {
        if($Number>0 && $Number<=7)
        {
            $this->Number = $Number;
        }

    }

    public function getBelowAbove()
    {
        return $this->Below_above;
    }

    public function setBelowAbove($Below_above)
    {
        $this->Below_above = $Below_above;
    }

    public function getWeekday()
    {
        return $this->Weekday;
    }

    public function setWeekday($Weekday)
    {
        $this->Weekday = $Weekday;
    }

    public function initialize()
    {
        $this->hasOne("Subject_id","Subjects","Subject_id");
        $this->hasOne("Group_id","Groups","ID_Group");
    }
}
