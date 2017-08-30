<?php

// GET the basic details of a staff member
$this->group('/staff', function() {

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
                $results = \ClassTestDetail::where('subject_detail_id', $subject['id'])->get();
                $filtered = collect($results)->filter(function($value, $key) {
                                                    return $value['obt_marks'] < $value['passing'];
                                                })->groupBy('ct_no');
    
                $sub = \SubjectDetail::find($subject['id']);
                array_push($res['my_subjects'], array(
                    'subject_id' => $sub['id'],
                    'subject_name' => ucwords($sub->pluck('name')->first()),
                    'below_avg_test_1' => $filtered->get('1')->count(),
                    'below_avg_test_2' => $filtered->get('2')->count()
                ));
            }
            return $response->withJson($res);
        }
        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });

    // GET self details
    $this->get('/self', function($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'staff') {
            $staff_id = $token['u_id'];
            $staff = \StaffDetail::find($staff_id)->get(['name', 'mob_no', 'email'])->first();
            // $staff['department'] = strtoupper($staff->dept->name);
            $staff['department'] = strtoupper(\DepartmentDetail::find($staff_id)->pluck('name')->first());
            
            // check if $staff if class teacher
            $class = \ClassTeacherMapping::where('staff_detail_id', $staff_id)->first();
            if ($class) {
                $my_class = \ClassMapping::find($class['class_mapping_id']);
                $staff['class'] = strtoupper($my_class->class);
                $staff['division'] = $my_class->division;
            }

            return $response->withJson($staff);
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
                                        ->orderBy('roll_no', 'asc')
                                        ->get(['id', 'roll_no', 'name'])->all();

            if ($students) {
                $below_avg = 0;
                foreach($students as $student) {
                    $below_avg_subjects = collect($student->marks()->get()->where('ct_no', $student->marks()->max('ct_no')))
                                            ->filter(function($item, $key) {
                                                return $item['obt_marks'] < $item['total_marks'];
                                            })->count();
                    $student['below_avg_subjects'] = $below_avg_subjects;
                    if ($below_avg_subjects > 0) {
                        $below_avg++;
                    }
                };
    
                return $response->withJson(array(
                    'total_students' => count($student),
                    'below_avg_count' => $below_avg,
                    'students' => $students
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
                                        ->orderBy('roll_no', 'desc')    // not sure why it sorts it in ascending order for 'desc'
                                        ->get(['id','roll_no','name']);

            if ($students) {
                $below_avg = 0;
                foreach($students as $student) {
                    $below_avg_subjects = collect($student->marks()->get()->where('ct_no', $student->marks()->max('ct_no')))
                                            ->filter(function($item, $key) {
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
    
    // GET the subject details (my_subjects)
    $this->get('/subjects/{sub_id}', function ($request, $response, $args) {
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
                $class_test = $subject->getClassTest()
                                    ->where('ct_no', $subject->getClassTest()->max('ct_no'))
                                    ->groupBy('student_detail_id')
                                    ->orderBy('student_detail_id')
                                    ->get();

                if ($class_test) {
                    $students = array();
                    foreach($class_test as $test) {
                        array_push($students, array(
                            'name' => $test->student->name,
                            'roll_no' => $test->student->roll_no,
                            'branch' => strtoupper($test->student->class->dept->name),
                            'class' => strtoupper($test->student->class->class),
                            'division' => $test->student->class->division,
                            'obt_marks' => (float) $test->obt_marks,
                            'total_marks' => (float) $test->total_marks,
                            'ct_no' => $subject->getClassTest()->max('ct_no'),
                            'ct_avg' => round($test->student->getAvg(), 2),
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

// GET the details of a particular student
$this->get('/student/{id}', function ($request, $response, $args) {
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();

    if ($token && $token['u_type'] == 'staff') {
        $staff = \StaffDetail::find($token['u_id']);
        $class = \ClassTeacherMapping::where('staff_detail_id', $staff['id'])->first();
        $student = \StudentDetail::find($args['id']);
        
        if ($student && $student['class_mapping_id'] == $class['class_mapping_id']) {
            $res = array(
                'id' => $student->id,
                'name' => $student->name,
                'prn_no' => $student->prn_no,
                'roll_no' => $student->roll_no,
                'dob' => $student->dob,
                'branch' => strtoupper($student->class->dept->name),
                'class' => strtoupper($student->class->class),
                'division' => $student->class->division,
                'mob_no' => $student->mob_no,
                'email' => $student->email,
                'ct_avg' => round($student->getAvg(), 2),
                'marks' => $student->marks()->get(['total_marks', 'obt_marks', 'ct_no'])
            );

            return $response->withJson($res);
        }
    }

    return $response->withJson(array('status'=>401,
                                    'error'=>'User not authorized'));
});

?>