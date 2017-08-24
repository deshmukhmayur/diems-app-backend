<?php

$app->post('/register', function($request, $response, $args) {
    // header("Content-Type: application/json");

    $token = $request->getQueryParams()['access_token'];
    $user = \AccessToken::where('token', $access_token)->first();

    if ($user) {
        $u_type = \AdminUser::where('username', $user['username'])->first()['u_type'];

        if ($u_type == 'admin') {
            // fetching POST parameters
            $params = $request->getBody();
            $data = json_decode($params, true);
            
            $username = $data['username'];
            $password = $data['password'];
            $u_type = $data['u_type'];
            
            try {
                // Creating a new Admin
                $admin = new \AdminUser(array(
                    'username' => $username,
                    'password' => hash('sha256', $password),
                    'u_type' => $u_type
                ));
                // Creating an access token
                $token = new \AccessToken(array(
                    'token' => md5($username . $password),
                    'username' => $username,
                    'u_type' => $u_type
                ));
                $admin->save();
                $token->save();
                return $response->withJson(array('status'=>201,
                                                'message'=>'User Created'));
            } catch (PDOException $e) {
                return $response->withJson(array('status'=>406,
                                                'message'=>'Username Already Exists'));
            }
        }
    }
    return $response->withJson(array('status'=>401,
                                    'error'=>'Unauthorized'));
});

$app->post('/login', function($request, $response, $args) {
    // header("Content-Type: application/json");

    // fetching POST parameters
    $params = $request->getBody();
    $data = json_decode($params, true);

    $username = $data['username'];
    $password = $data['password'];
    // $u_type = $data['u_type'];

    $user = \AdminUser::where('username', $username)->get()[0];

    if ($user['password'] == hash('sha256', $password)) {
        // return the access_token
        $token = \AccessToken::where('username', $username)->get()[0];
        return $response->withJson(array('status'=>202,
                                        'access_token'=>$token['token']));
    } else {
        return $response->withJson(array('status'=>401,
                                        'error'=>'Incorrect Username/Password'));
    }
});

$app->put('/changepass', function($request, $response) {
    // header("Content-Type: application/json");

    // getting the user from the access_token
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();

    // echo $token;

    // if the token exists
    if ($token) {
        $user = \AdminUser::where('username', $token['username'])->first();

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
                                            'access_token'=>$token['token']));
        } else {
            return $response->withJson(array('status'=>401,
                                            'message'=>'Incorrect Password'));
        }
    } else {
        return $response->withJson(array('status'=>401,
                                        'message'=>'Unauthorized Access'));
    }
});

?>