<?php
use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Http\Response;
use Phalcon\Http\Request as Request;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query;
use Phalcon\Session\Adapter\Files as Session;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Subject
{
    var $Subject_name;
    var $Subject_id;

    public function setSubjectName($Subject_name)
    {
        $this->Subject_name = $Subject_name;
    }

    public function setSubjectId($Subject_id)
    {
        $this->Subject_id = $Subject_id;
    }
}
class Teacher_shedule
{
    var $Subject_name;
    var $Subject_id;
    var $Group_name;
    var $Group_id;
    var $Number;
    var $Data;

    public function setSubjectName($Subject_name)
    {
        $this->Subject_name = $Subject_name;
    }

    public function setSubjectId($Subject_id)
    {
        $this->Subject_id = $Subject_id;
    }

    public function setGroupName($Group_name)
    {
        $this->Group_name = $Group_name;
    }

    public function setGroupId($Group_id)
    {
        $this->Group_id = $Group_id;
    }

    public function setNumber($Number)
    {
        $this->Number = $Number;
    }

    public function setData($Data)
    {
        $this->Data = $Data;
    }
}
class Tagged_students
{
    var $Student_ID;
    var $Name;
    var $Group_id;
}
$loader = new Loader();
$loader->registerDirs(
    array(
        __DIR__ . '/models/'
    )
)->register();

$di = new FactoryDefault();
$di->set('db', function () {
    return new DbAdapter(
        array(
            "host" => "localhost",
            "username" => "root2",
            "password" => "grizli",
            "dbname" => "attend",
            "charset" => 'UTF8'
        ));
});
$app = new Micro($di);
Model::setup(
    array(
        'phqlLiterals' => false
    )
);
/// залогиниться
$app->post('/auth', function () use ($app) {
    $login = $this->request->getPost('login');
    $password = $this->request->getPost('password');
    $user = Users::findFirst(array(
        "Login = :login: AND Password = :password:",
        'bind' => array(
            'login' => $login,
            'password' => $password
        )
    ));
    $teacher = Subjects::findFirst(array(
        "User_id=:userid:",
        'bind' => array(
            'userid' => $user->getUserId()
        )
    ));
    $steward = Students::findFirst(array(
        "User_id=:userid:",
        'bind' => array(
            'userid' => $user->getUserId()
        )
    ));
    $response = new Response();
    if ($user != false) {
        if ($teacher) {
            $response->setJsonContent(array(
                'Status' => 'OK',
                'Teacher' => $user->getUserId()
            ));
        } elseif ($steward) {
            $response->setJsonContent(array(
                'Status' => 'OK',
                'Group' => $steward->groups->getIDGroup()
            ));
        }
    } else {
        $response->setJsonContent("Error");
    }
    return $response;
});
/// показать список группы
$app->get('/list_of_group/{group_id}', function ($group_id) {

    $students = Students::find(array(
        "Group_id = :group_id:",
        "order" => 'Name',
        'bind' => array(
            'group_id' => $group_id
        )
    ));

    $response = new Response();

    if ($students) {
        foreach ($students as $student) {
            $listStudents[] = $student;
        }
        $response->setJsonContent(
            $listStudents
        );
    } else {
        $response->setJsonContent(array(
            'status' => "NOT FOUND"
        ));
    }
    return $response;
});
/// показать расписание
$app->get('/shedule/{group_id}', function ($group_id) {
    $date = new DateTime();
    $shedule = Shedule::find(array(
        "Group_id = :group: AND Weekday = :data:",
        "order" => 'Number',
        'bind' => array(
            'group' => $group_id,
            'data' => $date->format('Y-m-d')
        )
    ));
    $response = new Response();
    if (count($shedule->toArray()) != 0) {
        foreach ($shedule as $item) {
            $responseSub = new Subject();
            $responseSub->setSubjectId($item->subjects->getSubjectId());
            $responseSub->setSubjectName($item->subjects->getName());
            $shedulelist[] = $responseSub;
            unset($responseSub);
        }
        $response->setJsonContent(
            $shedulelist
        );
    } else {
        $response->setJsonContent(array(
            'status' => "NOT FOUND"
        ));
    }
    return $response;
});
/// показать посещаемость студента
$app->get('/attend_of_student/{student_id}/{subject}/{data}/{limit}', function ($student_id, $subject, $data, $limit) {

    $date = (new DateTime($data))->add(new DateInterval('P' . $limit . 'D'));
    $subject_id = Subjects::findFirst(array(
        "Name=:subject:",
        'bind' => array('subject' => $subject)
    ));
    $message = "SELECT * FROM Attend LEFT JOIN List_ ON Attend.Attend_id = List_.Attend_id WHERE Date BETWEEN :data1: AND :data2: AND Subject_id=:subject: AND Student_id=:student_id:";
//TODO: написать на чистом SQL без использования моделей (в конце статьи о PHQL( http://docs.phalconphp.ru/ru/latest/reference/phql.html#sql ))
    $query = new Query($message, $this->getDI());
    $result = $query->execute(array(
        "data1" => $data,
        "data2" => $date->format('Y-m-d'),
        "subject" => $subject_id->getName(),
        "student_id" => $student_id
    ))->toArray();
    $response = new Response();
    $response->setJsonContent([
        'status' => 'OK',
        'data' => $result
    ]);
    return $response;
});
/// показать посетивших пару в конкретную дату
$app->get('/attend/{subject}/{data}', function ($subject, $data) {
    $subject_id = Subjects::findFirst(array(
        "Name=:subject:",
        'bind' => array('subject' => $subject)
    ));
    $attend = Attend::findFirst(array(
        "Date = :data: AND Subject_id=:subject:",
        'bind' => array(
            'data' => $data,
            'subject' => $subject_id->getSubjectId()
        )
    ));
    $sql = "SELECT Name FROM Students LEFT JOIN List_ ON Students.Student_ID = List_.Student_id WHERE Attend_id=:attend_id:";
    $query = new Query($sql, $this->getDI());
    $result = $query->execute(array(
            "attend_id" => $attend->getAttendId()
        )
    )->toArray();
    $response = new Response();
    $response->setContentType('text/html', 'UTF8');
    $response->setJsonContent(array(
        'status' => 'OK',
        'data' => $result
    ));
    return $response;
});
/// получить список студентов отмеченых старостой
$app->get('/list_tag_stud/{group_id}/{data}/{subject_id}', function ($group_id,$data,$subject_id) {
    $response = new Response();
        $attend = Attend::findFirst(array(
            "Date = :data: AND Subject_id=:subject_id:",
            'bind' => array(
                'data' => $data,
                'subject_id' => $subject_id
            )
        ));
        $sql = "SELECT Student_ID,Group_id,Name,Attend_id FROM Students LEFT JOIN Temp_list ON Students.Student_ID = Temp_list.Student_Id WHERE Attend_id= :attend_id: AND Group_id=:group:";
        $query = new Query($sql, $this->getDI());
        $result = $query->execute(array(
            'attend_id' => $attend->getAttendId(),
            'group' => $group_id
        ));
    if(count($result->toArray())!=0)
    {
        //$lis[]=array();
        foreach($result as $item)
        {
            $tagged= new Tagged_students();
            $tagged->Student_ID=$item->Student_ID;
            $tagged->Name=$item->Name;
            $tagged->Group_id=$item->Group_id;
            $lis[]=$tagged;
        }
        $dat[]=[
          'Attend'=> $attend->getAttendId(),
          'Students'=>$lis
        ];
        $response->setJsonContent($dat);
        //var_dump($dat);
    }else{
        $response->setJsonContent(array(
            'status' => "NOT FOUND"
        ));
    }
    return $response;
});
/// есть ли отметки в этот день
$app->get('/isattend/{data}/{subject_id}', function ($data, $subject_id) {
    $attend = Attend::find(array(
        "Date=:data: AND Subject_id=:subject_id:",
        'bind' => array(
            'data' => $data,
            'subject' => $subject_id,
        )
    ));
    foreach ($attend as $item) {
        $arr[] = [
            "Attend_id" => $item->getAttendId(),
            "Subject_name" => $item->subjects->getName()
        ];
    }

    $response = new Response();
    if (!empty($arr)) {
        $response->setJsonContent($arr);
    }
    $response->send();
});
/// расписане преподавателя
$app->get('/teacher_shedule/{user_id}', function ($user_id) {
    $date = new DateTime();
    $date2 = new DateTime('last Monday');
    $shedule = Shedule::find(array(
        "Weekday BETWEEN :lastmonday: AND :now:",
        'bind' => array(
            'lastmonday' => $date2->format("Y-m-d"),
            'now' => $date->format("Y-m-d")
        )
    ));
    $attend =Attend::find(array(
        "Date BETWEEN :lastmonday: AND :now:",
        'bind'=>array(
            'lastmonday' => $date2->format("Y-m-d"),
            'now' => $date->format("Y-m-d")
        )
    ));
    $response = new Response();

    $period = new DatePeriod($date2, new DateInterval('P1D'), $date);
    $arrayOfDates = array_map(
        function($item){return $item->format('Y-m-d');},
        iterator_to_array($period)
    );

    foreach($attend as $item){
        $arrayattend[]=$item->getDate();
    }
    foreach($arrayOfDates as $item)
    {
        if(in_array($item,$arrayattend))
        {
            $newArrayOfDates[]=$item;
        }

    }
    if($newArrayOfDates!=null)
    {
        unset($arrayOfDates);
        $arrayOfDates=$newArrayOfDates;
        unset($newArrayOfDates);
    }
    $arrayOfDates=array_unique($arrayOfDates);
    if ($shedule) {
        foreach ($shedule as $item) {
            if(in_array($item->getWeekday(),$arrayOfDates))
            {
                if ($item->subjects->getUserId() == $user_id) {
                    $teacherShedule = new Teacher_shedule();
                    $teacherShedule->setSubjectName($item->subjects->getName());
                    $teacherShedule->setSubjectId($item->subjects->getSubjectId());
                    $teacherShedule->setGroupName($item->groups->getNameGroup());
                    $teacherShedule->setGroupId($item->groups->getIDGroup());
                    $teacherShedule->setNumber($item->getNumber());
                    $teacherShedule->setData($item->getWeekday());
                    $shedulelist[] = $teacherShedule;
                    unset($teacherShedule);
                }
            }

        }
        if (!empty($shedulelist)) {
            $response->setJsonContent(
                $shedulelist
            );
        }
    }
    return $response;

});
/// отметить посещаемость
$app->post('/attend', function () use ($app) {
    $response = new Response();
    $request = new Request();
    $data = new DateTime();
    $data = $data->format('Y:m:d');
    $req = $request->getJsonRawBody();
    $subject = $req->subject;
    $students = $req->students;
    $app->db->begin();
    $attend = new Attend();
    $attend->setSubjectId($subject);
    $attend->setDate($data);
    if ($attend->save() == false) {
        $app->db->rollback();
        return;
    }
    $attend_id = $attend->getAttendId();
    $phql1 = "INSERT INTO temp_list (Attend_id,Student_id) VALUES ";
    try {
        if (is_array($students)) {
            for ($i = 0; $i < count($students); $i++) {
                $phql1 .= "( " . $attend_id . ", " . $students[$i] . "), ";
            }
            $phql1 = substr($phql1, 0, strlen($phql1) - 2);
            $temp_list = new Temp_list();
            $result = $temp_list->getReadConnection()->query($phql1);
            if ($result) {
                ;
                $response->setJsonContent("OK");
            } else {
                $response->setJsonContent("Error");
            }
        }
    } catch (Exception $e) {
        $app->db->rollback();
        return;
    }
    $app->db->commit();
    return $response;
});
/// подтвердить посещаемость
$app->post('/submit_attend', function () use($app) {

    $response = new Response();
    $request = new Request();
    $req = $request->getJsonRawBody();
    $students = $req->students;
    $app->db->begin();
    $attend_id = $req->attend;
    $phql1 = "INSERT INTO list_ (Attend_id,Student_id) VALUES ";
    try {
        if (is_array($students)) {
            for ($i = 0; $i < count($students); $i++) {
                $phql1 .= "( " . $attend_id . ", " . $students[$i] . "), ";
            }
            $phql1 = substr($phql1, 0, strlen($phql1) - 2);
            $temp_list = new Temp_list();
            $result = $temp_list->getReadConnection()->query($phql1);
            if ($result) {
                ;
                $response->setJsonContent("OK");
            } else {
                $response->setJsonContent("Error");
            }
        }
    } catch (Exception $e) {
        $app->db->rollback();
        return;
    }
    $app->db->commit();
    return $response;
});

try{
    $app->handle();
}
catch (Exception $e){
    echo $e->getMessage();
    echo "/n";
    echo $e->getTraceAsString();
}

function getNumber($data)
{
    $number = 0;
    $cur_time = new DateTime();
    if ($cur_time > new DateTime('07:30') && $cur_time < new DateTime('09:00')) {
        $number = 1;
    } elseif ($cur_time > new DateTime('09:10') && $cur_time < new DateTime('10:40')) {
        $number = 2;
    }
    if ($cur_time > new DateTime('11:20') && $cur_time < new DateTime('12:50')) {
        $number = 3;
    } elseif ($cur_time > new DateTime('13:00') && $cur_time < new DateTime('14:30')) {
        $number = 4;
    } elseif ($cur_time > new DateTime('14:40') && $cur_time < new DateTime('16:10')) {
        $number = 5;
    } elseif ($cur_time > new DateTime('16:20') && $cur_time < new DateTime('17:50')) {
        $number = 6;
    } elseif ($cur_time > new DateTime('18:00') && $cur_time < new DateTime('19:30')) {
        $number = 7;
    }
    return $number;


}