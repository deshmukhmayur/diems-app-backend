<?php

$this->post('/register', function ($request, $response) {
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();

    if ($token && $token['u_type'] == 'admin') {
        $user = \AdminUser::find($token['u_id']);
        // return $response->withJson($user);
        
        if (!$user) {
            return $response->withStatus(500)->withJson(array(
                'status' => 500,
                'error' => 'Unexpected Internal Server Error'
            ));
        }

        // fetching POST parameters
        $params = $request->getBody();
        $data = json_decode($params, true);

        $user_type = $data['user_type'];
            
        try {
            if ($user_type == 'admin') {
                // Creating a new AdminUser
                $new_user = new \AdminUser(array(
                    'email' => $data['email'],
                    'password' => hash('sha256', $data['password']),
                    'type' => $data['u_type']
                ));
                $new_user->save();
            } elseif ($user_type == 'notice-admin') {
                $dept = \DepartmentDetail::where('name', $data['department'])->first();
                if (!$dept) {
                    return $response->withJson(array(
                        'status' => 404,
                        'error' => 'Department npt found'
                    ));
                }
                // Creating a new NoticeAdminUser
                $new_user = new \NoticeAdminUser(array(
                    'email' => $data['email'],
                    'password' => hash('sha256', $data['password']),
                    'type' => $data['u_type']
                ));
                $dept->noticeAdmins()->save($new_user);
            }
            elseif ($user_type == 'staff') {
                $dept = \DepartmentDetail::where('name', $data['dept'])->first();
                // Creating a new Staff user
                $new_user = new \StaffDetail(array(
                    'name' => $data['name'],
                    'mob_no' => $data['mob_no'],
                    'email' => $data['email'],
                    'password' => hash('sha256', $data['password']),
                ));
                $dept->staff()->save($new_user);
            } elseif ($user_type == 'student') {
                // fetch the class id for given branch, class, division
                $dept = \DepartmentDetail::where('name', $data['branch'])->first();
                $class = \ClassMapping::where('department_detail_id', $dept['id'])
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
                    'mentor_batch_mapping_id' => $batch['id'],
                    'dob' => $data['dob'],
                    'mob_no' => $data['mob_no'],
                    'email' => $data['email'],
                    'password' => hash('sha256', $data['password'])
                ));
                $class->students()->save($new_user);
            } else {
                return $response->withJson(array('status'=>400,
                                            'error'=>'Invalid User Type selected'));
            }
            // Creating an access token
            $token = new \AccessToken(array(
                'token' => md5($data['email'] . $data['password']),
                'u_id' => $new_user->id,
                'u_type' => $user_type
            ));
            $token->save();

            return $response->withJson(array('status'=>201,
                                        'message'=>'User Created'));
        } catch (PDOException $e) {
            if ($e->getCode() == "23000") {
                return $response->withJson(array('status'=>406,
                                            'error'=>'A User with the same email already exists'));
            } else {
                throw $e;
            }
        }
    } else {
        return $response->withJson(array('status'=>401,
                                        'error'=>'Unauthorized'));
    }
});

$this->post('/login', function ($request, $response) {
    // fetching POST parameters
    $params = $request->getBody();
    $data = json_decode($params, true);

    $username = $data['username'];
    $password = $data['password'];
    $u_type = $data['u_type'];

    if ($u_type == 'admin') {
        $user = \AdminUser::where('email', $username)->first();
    } elseif ($u_type == 'notice-admin') {
        $user = \NoticeAdminUser::where('email', $username)->first();
    } elseif ($u_type == 'staff') {
        $user = \StaffDetail::where('email', $username)->first();
    } elseif ($u_type == 'student') {
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

$this->put('/changepass', function ($request, $response) {
    // getting the user from the access_token
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();
    // echo $token;

    // if the token exists
    if ($token) {
        if ($token['u_type'] == 'admin') {
            $user = \AdminUser::find($token['u_id']);
        } elseif ($token['u_type'] == 'notice-admin') {
            $user = \NoticeAdminUser::find($token['u_id']);
        } elseif ($token['u_type'] == 'staff') {
            $user = \StaffDetail::find($token['u_id']);
        } elseif ($token['u_type'] == 'student') {
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

        if ($old_pass == $new_pass) {
            return $response->withJson(array('status' => 304,
                                            'message' => 'Password not changed.'));
        }

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
                                            'error'=>'The entered password is incorrect'));
        }
    } else {
        return $response->withJson(array('status'=>401,
                                        'error'=>'Unauthorized Access'));
    }
});
