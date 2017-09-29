<?php

use Slim\Http\UploadedFile;
use \phpOffice\phpexcel\Classes\PHPExcel\IOFactory;

// GET the basic details of a staff member
$this->group('/staff', function () {

    // GET the dashboard details
    $this->get('', function ($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'staff') {
            // initialize an empty response object
            $res = array(
                'my_class' => null,
                'my_mentees' => null,
                'my_subjects' => []
            );
    
            $staff_id = $token['u_id'];
    
            // check if $staff is a class teacher
            $class = \ClassTeacherMapping::where('staff_detail_id', $staff_id)->first();
            if ($class) {
                // fetch and push the details in $res['my_class']
                $students = \StudentDetail::where('class_mapping_id', $class['class_mapping_id'])->get();

                $res['my_class'] = array(
                    'branch' => strtoupper($students->first()->class->dept->name),
                    'class' => strtoupper($students->first()->class->class),
                    'division' => $students->first()->class->division,
                    'total_students' => $students->count(),
                );
            }
    
            // fetch the mentor batch for the staff
            $batch = \MentorBatchMapping::where('staff_detail_id', $staff_id)->first();
            if ($batch) {
                // fetch and push the details in $res['mentees']
                $students = \StudentDetail::where('mentor_batch_mapping_id', $batch['id']);
                $res['my_mentees'] = array(
                    'branch' => strtoupper($students->first()->class->dept->name),
                    'class' => strtoupper($students->first()->class->class),
                    'division' => $students->first()->class->division,
                    'batch' => strtoupper($students->first()->batch->batch_name),
                    'total_students' => $students->count(),
                );
            }
    
            $subjects_list = \SubjectTeacherMapping::where('staff_detail_id', $staff_id)->get();
            foreach ($subjects_list as $subject) {
                // fetch all the class test results for the subjects
                $results = \ClassTestDetail::where('subject_detail_id', $subject['subject_detail_id'])->get();
                $filtered = collect($results)->filter(function ($value, $key) {
                                                    return $value['obt_marks'] < $value['passing'];
                })->groupBy('ct_no');
                                                
                $sub = \SubjectDetail::find($subject['id']);
                array_push($res['my_subjects'], array(
                    'subject_id' => $sub['id'],
                    'subject_name' => ucwords($sub->name),
                    'below_avg_test_1' => count($filtered->get('1')),
                    'below_avg_test_2' => count($filtered->get('2'))
                ));
            }
            return $response->withJson($res);
        }
        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });

    // GET self details
    $this->get('/self', function ($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'staff') {
            $staff_id = $token['u_id'];
            $staff = \StaffDetail::find($staff_id);
            
            $staff['department'] = strtoupper($staff->dept->name);
            
            // check if $staff if class teacher
            $class = \ClassTeacherMapping::where('staff_detail_id', $staff_id)->first();
            if ($class) {
                $my_class = \ClassMapping::find($class['class_mapping_id']);
                $staff['class'] = strtoupper($my_class->class);
                $staff['division'] = $my_class->division;
            }

            // return $response->withJson($staff);
            return $response->withJson(array(
                'name' => ucwords($staff->name),
                'mob_no' => $staff->mob_no,
                'email' => $staff->email,
                'department' => $staff->department
            ));
        }

        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });

    // GET the class details (my_class)
    $this->get('/class', function ($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'staff') {
            $staff = \StaffDetail::find($token['u_id']);
            $class = \ClassTeacherMapping::where('staff_detail_id', $staff['id'])->first();
            $students = \StudentDetail::where('class_mapping_id', $class['class_mapping_id'])
                                        ->orderBy('roll_no', 'asc')->get();
            if ($students) {
                $below_avg = 0;
                foreach ($students as $student) {
                    $below_avg_subjects = collect($student->marks()->get()->where('ct_no', $student->marks()->max('ct_no')))
                                            ->filter(function ($item, $key) {
                                                return $item['obt_marks'] < $item['total_marks'];
                                            })->count();
                    $student['below_avg_subjects'] = $below_avg_subjects;
                    if ($below_avg_subjects > 0) {
                        $below_avg++;
                    }
                };

                $stud_class = $student->class->class . ' ' . $student->branch()->name . ' ' . $student->class->division;

                return $response->withJson(array(
                    'total_students' => count($student),
                    'below_avg_count' => $below_avg,
                    'class' => strtoupper($stud_class),
                    'students' => $students
                ));
            } else {
                return $response->withJson(array(
                    'status' => 404,
                    'error' => 'No data found'
                ));
            }
        }
    
        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });
    
    // GET the mentee details (my_mentees)
    $this->get('/mentees', function ($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'staff') {
            $staff = \StaffDetail::find($token['u_id']);
            $batch = \MentorBatchMapping::where('staff_detail_id', $staff['id'])->first();
            $students = \StudentDetail::where('mentor_batch_mapping_id', $batch['id'])
                                        ->orderBy('roll_no', 'asc')
                                        ->get(['id','roll_no','name']);

            if ($students) {
                $below_avg = 0;
                foreach ($students as $student) {
                    $below_avg_subjects = collect($student->marks()->get()->where('ct_no', $student->marks()->max('ct_no')))
                                            ->filter(function ($item, $key) {
                                                return $item['obt_marks'] < $item['total_marks'];
                                            })->count();
                    $student['below_avg_subjects'] = $below_avg_subjects;
                    if ($below_avg_subjects > 0) {
                        $below_avg++;
                    }
                };
    
                return $response->withJson(array(
                    'total_students' => count($students),
                    'below_avg_count' => $below_avg,
                    'students' => $students
                ));
            }

            return $response->withJson($students);
        }
    
        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });
    
    $this->group('/subjects', function () {

        $this->get('', function ($request, $response) {
            $access_token = $request->getQueryParams()['access_token'];
            $token = \AccessToken::where('token', $access_token)->first();
        
            if ($token && $token['u_type'] == 'staff') {
                $staff = \StaffDetail::find($token['u_id']);

                $subjects_list = \SubjectTeacherMapping::where('staff_detail_id', $staff['id'])
                                                        ->get();
                
                if (count($subjects_list) > 0) {
                    $subjects = array();

                    foreach ($subjects_list as $sub) {
                        array_push($subjects, array(
                            'id' => $sub->subject->id,
                            'name' => ucwords($sub->subject->name)
                        ));
                    }

                    return $response->withJson(array(
                        'status' => 200,
                        'subjects' => $subjects
                    ));
                }
                return $response->withJson(array(
                    'status' => 404,
                    'error' => 'No data found.'
                ));
            }

            return $response->withJson(array('status'=>401,
                                            'error'=>'User not authorized'));
        });

        // GET the subject details (my_subjects)
        $this->get('/{sub_id}', function ($request, $response, $args) {
            $access_token = $request->getQueryParams()['access_token'];
            $token = \AccessToken::where('token', $access_token)->first();
        
            if ($token && $token['u_type'] == 'staff') {
                $staff = \StaffDetail::find($token['u_id']);
                $subject = \SubjectDetail::find($args['sub_id']);
    
                if (!$subject) {
                    return $response->withJson(array(
                        'status' => 404,
                        'error' => 'Invalid subject id'
                    ));
                } else {
                    $class_test = $subject->getClassTest
                                        ->groupBy('student_detail_id')
                                        ->all();
    
                    if ($class_test) {
                        $students = array();
                        foreach ($class_test as $stud_id => $marks) {
                            $stud = \StudentDetail::find($stud_id);
    
                            $stud_marks = array();
                            foreach ($marks as $m) {
                                array_push($stud_marks, array(
                                    'obt_marks' => $m->obt_marks,
                                    'total_marks' => $m->total_marks,
                                    'ct_no' => $m->ct_no,
                                ));
                            }
    
                            
                            for ($i=count($stud_marks); $i < 2; $i++) {
                                array_push($stud_marks, array(
                                    'obt_marks' => 'N/A',
                                    'total_marks' => 'N/A',
                                    'ct_no' => $i+1,
                                ));
                            }
    
                            array_push($students, array(
                                'name' => ucwords($stud->name),
                                'roll_no' => $stud->roll_no,
                                'branch' => strtoupper($stud->class->dept->name),
                                'class' => strtoupper($stud->class->class),
                                'division' => $stud->class->division,
                                'marks' => $stud_marks,
                                'ct_avg' => ceil($stud->getSubAvg($args['sub_id'])),
                            ));
                        }
    
                        return $response->withJson(array(
                            'subject_name' => ucwords($subject->name),
                            'students' => $students
                        ));
                    }
                }
            }
            
            return $response->withJson(array('status'=>401,
                                            'error'=>'User not authorized'));
        });
    });

    // POST method for adding data (i.e. Users / Class Test Results)
    $this->post('/add-marks', function ($request, $response) {
        $queryString = $request->getQueryParams();
        $access_token = $queryString['access_token'];
        // return $response->withJson($access_token);
        $token = \AccessToken::where('token', $access_token)->first();

        if ($token && $token['u_type'] == 'staff') {
            $staff = \StaffDetail::find($token->u_id);
        
            $uploadedFiles = $request->getUploadedFiles();
            // handle single input with single file upload
            $uploadedFile = $uploadedFiles['file'];
            return $response->withJson($uploadedFiles);
            
            $class = \ClassTeacherMapping::where('staff_detail_id', $staff->id)->first()->class;
            
            if ($uploadedFile && $uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $class->dept->name . $class->class . $class->division;
                // error_log($filename);
                $filepath = moveUploadedFile(ROOT . '/assets/results', $filename, $uploadedFile);
        
                /** TODO: Process the uploaded file
                * get the ct_no, passing_marks, total_marks from $queryString
                * get the class from $class
                * verify if the roll_no are from the class?
                * get the $stud_id from roll_no
                * get the $subject_detail_id from subject_name
                * insert the rows in \ClassTestDetail
                */

                return $response->withJson(array(
                    'status' => 201,
                    'message' => 'Data uploaded successfully',
                    'body' => $request->getParsedBody()
                ));
            }

            return $response->withJson(array(
                'status' => 400,
                'error' => 'Error occured while uploading. Please try again.'
            ));
        }
        return $response->withJson(array(
        'status' => 401,
        'error' => 'User not authorized'
        ));
    });

    // GET the details of a student
    $this->get('/search', function ($request, $response) {
        $queryString = $request->getQueryParams();
        $access_token = $queryString['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();

        if ($token && $token['u_type'] == 'staff') {
            try {
                $staff = \StaffDetail::find($token->u_id);
                $classes = $staff->dept->classes->pluck('id');
                // return $response->withJson($staff->mentees->students);

                if (!array_key_exists('stud_group', $queryString)) {
                    throw new Exception("No student group selected", 1);
                } else {
                    if ($queryString['stud_group'] == 'class') {
                        $students = $staff->class->class->students();
                    } else if ($queryString['stud_group'] == 'mentees') {
                        $students = $staff->mentees->students();
                    } else {
                        throw new Exception("Invalid student group selected", 1);
                    }
                
                    if (array_key_exists('stud_name', $queryString)) {
                        $students = $students->where('name', 'LIKE', $queryString['stud_name'] . '%');
                        /* returns {
                        *     '1': {student object}
                        * }
                        * not sure why it does that
                        */
                    }
                    if (array_key_exists('roll_no', $queryString)) {
                        $students = $students->where('roll_no', $queryString['roll_no']);
                    }
                    if (array_key_exists('prn_no', $queryString)) {
                        $students = $students->where('prn_no', $queryString['prn_no']);
                    }
                }

                return $response->withJson($students->get());
            } catch (Exception $e) {
                return $response->withJson(array(
                    'status' => 412,
                    'error' => $e->getMessage()
                ));
            }
        }

        return $response->withJson(array(
            'status' => 401,
            'error' => 'User unauthorized'
        ));
    });
});

// GET the details of a student
$this->group('/students', function () {
    
    // GET self details
    $this->get('/marks', function ($request, $response) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'student') {
            $stud_id = $token['u_id'];
            $student = \StudentDetail::find($stud_id);
            
            $marks = $student->marks
                            ->groupBy('subject_detail_id')
                            ->all();
            
            $sub_array = array();
            foreach ($marks as $sub_id => $mark) {
                        $filtered_marks = array();
                foreach ($mark as $m) {
                    array_push($filtered_marks, array(
                    'obt_marks' => $m->obt_marks,
                    'total_marks' => $m->total_marks,
                    'ct_no' => $m->ct_no,
                    ));
                }

                for ($i=count($filtered_marks); $i < 2; $i++) {
                    array_push($filtered_marks, array(
                        'obt_marks' => 'N/A',
                        'total_marks' => 'N/A',
                        'ct_no' => $i+1,
                    ));
                }
                array_push($sub_array, array(
                    'subject_name' => ucwords(\SubjectDetail::find($sub_id)->name),
                    'class_test' => $filtered_marks,
                ));
            }

            $stud_class = $student->class->class . $student->branch()->name . '-' . $student->class->division;

            return $response->withJson(array(
                'status' => '200',
                'roll_no' => $student->roll_no,
                'name' => ucwords($student->name),
                'class' => strtoupper($stud_class),
                'marks' => $sub_array
            ));
        }

        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });
    
    // GET the details of a particular student
    $this->get('/{id}', function ($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'staff') {
            $staff = \StaffDetail::find($token['u_id']);
            $class = \ClassTeacherMapping::where('staff_detail_id', $staff['id'])->first();
            $student = \StudentDetail::find($args['id']);
            
            if ($student && $student['class_mapping_id'] == $class['class_mapping_id']) {
                $res = array(
                    'id' => $student->id,
                    'name' => ucwords($student->name),
                    'prn_no' => $student->prn_no,
                    'roll_no' => $student->roll_no,
                    'dob' => $student->dob,
                    'branch' => strtoupper($student->class->dept->name),
                    'class' => strtoupper($student->class->class),
                    'division' => $student->class->division,
                    'mob_no' => $student->mob_no,
                    'email' => $student->email,
                    'ct_avg' => round($student->getAvg(), 2)
                );
    
                $marks = $student->marks
                                ->groupBy('subject_detail_id')
                                ->all();
                                
                $sub_array = array();
                foreach ($marks as $sub_id => $mark) {
                    $filtered_marks = array();
                    foreach ($mark as $m) {
                        array_push($filtered_marks, array(
                            'obt_marks' => $m->obt_marks,
                            'total_marks' => $m->total_marks,
                            'ct_no' => $m->ct_no,
                        ));
                    }
    
                    for ($i=count($filtered_marks); $i < 2; $i++) {
                        array_push($filtered_marks, array(
                            'obt_marks' => 'N/A',
                            'total_marks' => 'N/A',
                            'ct_no' => $i+1,
                        ));
                    }
    
                    array_push($sub_array, array(
                        'subject_name' => ucwords(\SubjectDetail::find($sub_id)->name),
                        'class_test' => $filtered_marks,
                    ));
                }
    
                $res['marks'] = $sub_array;
    
                return $response->withJson($res);
            }
        }
    
        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });
});

$this->get('/test', function ($request, $response) {
    $xls = PHPExcel_IOFactory::load(ROOT . '/assets/results/csebe1_20170926180456.xls');
    $sheet = $xls->getActiveSheet();
    $maxCell = $sheet->getHighestRowAndColumn();
    $res = $sheet->toArray(null);

    $header = $res[0];
    $header[0] = 'roll_no';
    $header = array_map(function ($item) {
        return strtolower($item);
    }, $header);

    $marklist = array_slice($res, 1, $maxCell['row']);

    $r = array();
    foreach ($marklist as $row) {
        $c = array(
            'roll_no' => $row[0]
        );
        $subs = array();
        for ($i=1; $i < count($row); $i++) {
            // $c[$header[$i]] = $row[$i];
            array_push($subs, array(
                'subject_name' => $header[$i],
                'obt_marks' => $row[$i]
            ));
        }
        $c['subjects'] = $subs;
        array_push($r, $c);
    }

    return $response->withJson($r);
});



function moveUploadedFile($directory, $filename, UploadedFile $uploadedFile)
{
    // error_log($directory);
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    // $basename = $filename . '_' . bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $basename = $filename . '_' . date('YmdHis');
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

?>