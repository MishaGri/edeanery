<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Query;
use Phalcon\Http\Response;
use Phalcon\Http\Request;

class reportsController extends Controller
{
    public function initialize()
    {
        $this->assets
            ->addCss("css/bootstrap.min.css");
        $this->assets
            ->addJs("js/bootstrap.min.js");
    }

    public function indexAction()
    {

    }

    public function sheduleAction()
    {
        $response= new Response();
        $this->tag->setTitle("Расписание");
        $request= new Request();
        if($request->isPost())
        {
            if($request->getPost("group")!=null)
            {
                $date = "2016-05-30";//=new DateTime();///TODO: заменить на текущий день
                //$data->format("Y-m-d");
                $group_id=$request->getPost("group");
                $shedule = Shedule::find(array(
                    "Group_id = :group: AND Weekday = :data:",
                    "order" => 'Number',
                    'bind' => array(
                        'group' => $group_id,
                        'data' => $date
                    )
                ));
                foreach($shedule as $subject)
                {
                    $temp=array(
                        'Weekday'=>$subject->getWeekday(),
                        'Number'=>$subject->getNumber(),
                        'Name'=>$subject->subjects->getName()
                    );
                    $subjectt[]=$temp;
                    unset($temp);
                }
                $response->setStatusCode(200);
                $response->setJsonContent($subjectt);
                return $response;
            }else{
                $response->setStatusCode(500);
                return $response;
            }
        }
        else{
            $groups=Groups::find();
            $this->view->setVar("groups",$groups);
        }
    }

    public function list_of_groupAction()
    {
        $response= new Response();
        $request= new Request();
        $this->tag->setTitle("Список групп");
        if($request->isPost())
        {
            if($request->getPost("group"))
            {
                $group=$request->getPost("group");
                $students=Students::find(array(
                    "Group_id=:group:",
                    "order"=>'Name',
                    'bind'=>array(
                        'group'=>$group
                    )
                ));
                $response->setStatusCode(200);
                $response->setJsonContent($students);
                return $response;
            }else{
                $response->setStatusCode(500);
                return $response;
            }
        }else{
            $groups=Groups::find();
            $this->view->setVar("groups",$groups);
        }
    }

    public function attend_studentAction()
    {
        $student_name = 'Петров А.Б.';
        $data1 = '2016-05-07';
        $data2 = '2016-05-10';
        $subject = 'Предмет';
        $message = "SELECT * FROM Attend LEFT JOIN List_ ON Attend.ID = List_.id WHERE Date BETWEEN :data1: AND :data2: AND Subject=:subject: AND Student_ID=(SELECT Studet_ID FROM Students WHERE Student_name=:name:)";
        $query = new Query($message, $this->getDI());
        $result = $query->execute(array(
            "data1" => $data1,
            "data2" => $data2,
            "subject" => $subject,
            "name" => $student_name
        ))->toArray();
        $response = new Response();
        $response->setJsonContent(array(
            'status' => 'OK',
            'data' => $result
        ));
        return $response;
    }

    public function attendAction()
    {
        $data = '2016-05-07';
        $subject = 'Предмет';
        $message = "SELECT Student_name FROM Students LEFT JOIN List_ ON Students.Studet_ID = List_.Student_ID WHERE id=(SELECT ID FROM Attend WHERE Date = :data: AND Subject=:subject:)";
        $query = new Query($message, $this->getDI());
        $result = $query->execute(array(
                "data" => $data,
                "subject" => $subject
            )
        );
        var_dump($result);
    }
}