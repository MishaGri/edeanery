<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class excelController extends Controller
{

    public function initialize()
    {
        $this->assets
            ->addCss("css/bootstrap.min.css");
    }

    public function indexAction()
    {
        $this->tag->setTitle("Редактирование расписания");
    }

    public function checkAction()
    {
        require_once("PHPExcel/Classes/PHPExcel.php");
        $response = new Response();
        if ($this->request->hasFiles()) {
            foreach ($this->request->getUploadedFiles() as $file) {
                if ($file->getKey() == 'userfile') {
                    $file->moveTo("../userfiles/" . $file->getName());
                    $filename = "../userfiles/" . $file->getName();
                    $excelReader = PHPExcel_IOFactory::createReaderForFile($filename);
                    $excelObj = $excelReader->load($filename);
                    $excelObj->setActiveSheetIndex(0);
                    $worksheet = $excelObj->getActiveSheet();
                    $lastcolumn = $worksheet->getHighestColumn();
                    $lastrow = $worksheet->getHighestRow();
                    $lastcolumn++;
                    /////находим все группы
                    for ($column = 'C'; $column != $lastcolumn; $column++) {
                        $cell = $worksheet->getCell($column . 1);
                        $groups[] = $cell->getValue();
                    }
                    try {
                        $sql = "SELECT ID_Group FROM Groups WHERE Name_group IN (";
                        foreach ($groups as $item) {
                            $sql .= "'" . $item . "', ";
                        }
                        $sql = substr($sql, 0, strlen($sql) - 2);
                        $sql .= ")";
                        $reslt = $this->modelsManager->executeQuery($sql);
                        unset($groups);
                        unset($sql);
                        foreach ($reslt as $item) {
                            $groups[] = $item->ID_Group;
                        }
                    } catch (Exception $e) {
                        echo $e->getMessage() . "<br>";
                        echo $e->getTraceAsString();
                    }
                    ////получаем данные из файла
                    try {
                        for ($column = 'A'; $column != $lastcolumn; $column++) {
                            for ($row = 2; $row <= $lastrow; $row++) {
                                $cell = $worksheet->getCell($column . $row);
                                $temparr[] = $cell->getValue();
                            }
                            if (!empty($temparr)) {
                                $data[] = $temparr;
                                unset ($temparr);
                            }

                        }
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        echo $e->getTraceAsString();
                    }
                    //получаем дни недели
                    foreach ($data[0] as $day) {
                        if ($day != null)
                            $days[] = $day;
                    }
                    //уникальные названия предметов для поиска в базе
                    $subjectids = array_unique($data[2]);
                    //получаем расписание на дни на неделю под\над чертой
                    try {
                        $sql = "SELECT Subject_id FROM Subjects WHERE Name IN (";
                        foreach ($subjectids as $item) {
                            $sql .= "'" . $item . "', ";
                        }
                        $sql = substr($sql, 0, strlen($sql) - 2);
                        $sql .= ")";
                        $reslt = $this->modelsManager->executeQuery($sql);
                        //var_dump($subjectids);
                        unset($sql);
                        $i = 0;
                        $temp4 = [];
                        foreach ($reslt as $item) {
                            $temp4[$subjectids[$i]] = $item->Subject_id;
                            $i++;
                        }
                        //var_dump($temp4);
                        unset($subjectids);
                        $subjectids = $temp4;
                        unset($temp4);
                    } catch (Exception $e) {
                        echo $e->getMessage() . "<br>";
                        echo $e->getTraceAsString();
                    }
                    for ($k = 0; $k < count($groups); $k++) {
                        for ($i = 0; $i < count($data[0]) / 14; $i++) {
                            for ($j = $i * 14; $j < ($i + 1) * 14; $j++) {
                                $temp[] = $data[2 + $k][$j];
                            }
                            $subjects[] = $temp;
                            unset ($temp);
                        }
                        $subjectsofgroup[] = $subjects;
                        unset ($subjects);
                    }
                    //получение расписания на дни above-надчертой, below-подчертой
                    foreach ($subjectsofgroup as $subjects) {
                        foreach ($subjects as $subject) {
                            for ($i = 0; $i < count($subject); $i++) {
                                ////если четный
                                if ($i & 1) {
                                    $temp1[] = $subject[$i];
                                } else {
                                    $temp2[] = $subject[$i];
                                }
                            }
                            $above[] = $temp2;
                            $below[] = $temp1;
                            unset($temp1);
                            unset($temp2);
                        }
                        $temp3['above'] = $above;
                        $temp3['below'] = $below;
                        unset($above);
                        unset($below);
                        $groupsubjects[] = $temp3;
                        unset($temp3);
                    }
                    /*var_dump($groupsubjects[0]['above']);
                    var_dump($groupsubjects[0]['below']);
                    var_dump($groupsubjects[0]);
                    var_dump($groups);
                    var_dump($subjectids);
                    var_dump($days);*/
                    $dat = new DateTime();
                    //заносим в базу
                    for ($group = 0; $group < count($groups); $group++)//для каждой группы
                    {
                        $sql = "INSERT INTO Shedule (Subject_id, Group_id, Number, Below_above, Weekday) VALUES ";
                        $shedule = new Shedule();
                        for ($ab = 0; $ab < count($groupsubjects[$group]); $ab++)// для недели над\под чертой
                        {
                            for ($day = 0; $day < count($days); $day++) {
                                if ($ab == 0) {
                                    for ($subject = 0; $subject < count($groupsubjects[$group]['above'][$day]); $subject++)//каждый предмет добавляем в БД
                                    {
                                        $sql .= "('" . $subjectids[$groupsubjects[$group]['above'][$day][$subject]] . "', '" . $groups[$group] . "', '" . ($subject + 1) . "','1', '" . $dat->format("Y-m-d") . "'), ";
                                        if ($subject == count($groupsubjects[$group]['above'][$day]) - 1) {
                                            $sql = substr($sql, 0, strlen($sql) - 2);
                                        }
                                    }
                                } else {
                                    for ($subject = 0; $subject < count($groupsubjects[$group]['below'][$day]); $subject++)//каждый предмет добавляем в БД
                                    {
                                        $sql .= "('" . $subjectids[$groupsubjects[$group]['below'][$day][$subject]] . "', '" . $groups[$group] . "', '" . ($subject + 1) . "','1', '" . $dat->format("Y-m-d") . "'), ";
                                        if ($subject == count($groupsubjects[$group]['below'][$day]) - 1) {
                                            $sql = substr($sql, 0, strlen($sql) - 2);
                                        }
                                    }
                                }
                                try {
                                    //var_dump($sql);
                                    echo "<br>";
                                    $shedule->getReadConnection()->query($sql);
                                    $sql = "INSERT INTO Shedule (Subject_id, Group_id, Number, Below_above, Weekday) VALUES ";
                                } catch (Exception $e) {
                                    echo $e->getMessage() . "<br>";
                                    echo $e->getTraceAsString();
                                }
                            }
                        }
                    }
                }
            }
            $response->setStatusCode(200);
            return $response;
        } else {
            $response->setStatusCode(500);
            return $response;
            echo 'Возникли некоторые проблемы';
        }
    }
}