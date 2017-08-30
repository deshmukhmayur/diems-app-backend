<?php

$this->post('/register', function($request, $response, $args) {
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();

    if ($token) {
        $user = \AdminUser::find($token['u_id']);
        // return $response->withJson($user);

        if ($user['u_type'] == 'admin') {
            // fetching POST parameters
            $params = $request->getBody();
            $data = json_decode($params, true);

            $user_type = $data['user_type'];
            
            try {
                if ($user_type == 'admin') {
                    // Creating a new Admin
                    $new_user = new \AdminUser(array(
                        'email' => $data['email'],
                        'password' => hash('sha256', $data['password']),
                        'u_type' => $data['u_type']
                    ));
                } else if ($user_type == 'staff') {
                    $dept = \DepartmentDetail::where('name', $data['dept'])->first();
                    // Creating a new Staff user
                    $new_user = new \StaffDetail(array(
                        'name' => $data['name'],
                        'deptartment_detail_id' => $dept['id'],
                        'mob_no' => $data['mob_no'],
                        'email' => $data['email'],
                        'password' => hash('sha256', $data['password']),
                    ));
                } else if ($user_type == 'student') {
                    // fetch the class id for given branch, class, division
                    $dept = \DepartmentDetail::where('name', $data['branch'])->first();
                    $class = \ClassMapping::where('department_detail_id',$dept['id'])
                                            ->where('class', $data['class'])
                                            ->where('division', $data['division'])
                                            ->first();
                    $batch = \MentorBatchMapping::where('batch_name', $data['batch'])
                                                ->first();
                    // Creating a new student user
                    $new_user = new \StudentDetail(array(
                        'name' => $data['name'],
                        'prn_no' => $data['prn_no'],
                        'roll_no' => $data['roll_no'],
                        'class_mapping_id' => $class['id'],
                        'mentor_batch_mapping_id' => $batch['id'],
                        'dob' => $data['dob'],
                        'mob_no' => $data['mob_no'],
                        'email' => $data['email'],
                        'password' => hash('sha256', $data['password'])
                    ));

                    // return $response->withJson(array(
                    //     'name' => $data['name'],
                    //     'prn_no' => $data['prn_no'],
                    //     'roll_no' => $data['roll_no'],
                    //     'class_mapping_id' => $class['id'],
                    //     'mentor_batch_mapping_id' => $batch['id'],
                    //     'dob' => $data['dob'],
                    //     'mob_no' => $data['mob_no'],
                    //     'email' => $data['email'],
                    //     'password' => hash('sha256', $data['password'])
                    // ));
                    // return $response->withJson(array($dept, $class, $batch));
                } else {
                    return $response->withJson(array('status'=>400,
                                                    'error'=>'Invalid User Type selected'));
                }
                $new_user->save();
                // Creating an access token
                $token = new \AccessToken(array(
                    'token' => md5($data['username'] . $data['password']),
                    'u_id' => $new_user['id'],
                    'u_type' => $user_type
                ));
                $token->save();
                return $response->withJson(array('status'=>201,
                                                'message'=>'User Created'));
            } catch (PDOException $e) {
                return $response->withJson(array('status'=>406,
                                                'message'=>'Username Already Exists',
                                                'error'=>$e));
            }
        }
    } else {
        return $response->withJson(array('status'=>401,
                                        'error'=>'Unauthorized'));
    }
});

$this->post('/login', function($request, $response, $args) {
    // fetching POST parameters
    $params = $request->getBody();
    $data = json_decode($params, true);

    $username = $data['username'];
    $password = $data['password'];
    $u_type = $data['u_type'];

    if ($u_type == 'admin') {
        $user = \AdminUser::where('email', $username)->first();
    } else if ($u_type == 'staff') {
        $user = \StaffDetail::where('email', $username)->first();
    } else if ($u_type == 'student') {
        $user = \StudentDetail::where('email', $username)->first();
    } else {
        return $response->withJson(array('status'=>400,
                                        'error'=>'Invalid user type'));
    }

    if ($user['password'] == hash('sha256', $password)) {
        // return the access_token
        $token = \AccessToken::where('u_type', $u_type)
                                ->where('u_id', $user['id'])->first();
        return $response->withJson(array('status'=>202,
                                        'message' => 'Login Successful',
                                        'access_token'=>$token['token'],
                                        'username' => $user['email']
                                    ));
    } else {
        return $response->withJson(array('status'=>401,
                                        'error'=>'Incorrect Username/Password'));
    }
});

$this->put('/changepass', function($request, $response) {
    // getting the user from the access_token
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();
    // echo $token;

    // if the token exists
    if ($token) {
        if ($token['u_type'] == 'admin') {
            $user = \AdminUser::find($token['u_id']);
        } else if ($token['u_type'] == 'staff') {
            $user = \StaffDetail::find($token['u_id']);
        } else if ($token['u_type'] == 'student') {
            $user = \StudentDetail::find($token['u_id']);
        } else {
            return $response->withJson(array('status'=>500,
                                            'error'=>'Internal Server Error'));
        }
        // echo $user;

        // fetching POST parameters
        $params = $request->getBody();
        $data = json_decode($params, true);

        $old_pass = $data['old_pass'];
        $new_pass = $data['new_pass'];
        // echo $old_pass .'<br>'. $new_pass;

        if (hash('sha256', $old_pass) == $user['password']) {
            // updating the password and token
            $user->update([
                'password' => hash('sha256', $new_pass)
                ]);
            $token->update([
                'token' => md5($user['username'] . $new_pass)
                ]);
            $user->save();
            $token->save();

            return $response->withJson(array('status'=>202,
                                            'message'=>'Password successfully changed',
                                            'access_token' => $token['token']));
        } else {
            return $response->withJson(array('status'=>412,
                                            'message'=>'Incorrect Password'));
        }
    } else {
        return $response->withJson(array('status'=>401,
                                        'message'=>'Unauthorized Access'));
    }
});

?>