<?php

// Include the Notice model
require '../models/Notice.php';
// Include the admin module
require '../models/Admin.php';
// Include the access token module
require '../models/AccessToken.php';

$app->get('/notices', function($request, $response, $args) {
    
    header("Content-Type: application/json");
    
    // Fetch all books
    $notices = \NoticeDetail::orderBy('created_at', 'desc')->get();
    $response->getBody()->write($notices->toJson());
    return $response;
});

$app->post('/notice', function($request, $response, $args) {

    // header("Content-Type: application/json");
    
    // getting the user from the access_token
    $access_token = $request->getQueryParams('access_token')['access_token'];
    $user = \AccessToken::where('token', $access_token)->get()[0];

    if ($user) {
        // getting the request body
        $json = $request->getBody();
        $data = json_decode($json, true);

        // fetching the u_id of the current user
        $u_id = \AdminUser::where('username', $user['username'])->first()['u_id'];
        // echo $u_id;

        // error_log(print_r("Response: \n" . $data), 4);
        // echo $json . '<br><br>';
        // echo $data['image'];

        $img_url = '';
        // if request body contains image, save it to uploads/
        // and update the $img_url
        if (array_key_exists('image', $data)) {
            $image = base64_decode($data['image']);
            $filename = uniqid().'.jpg';
            $file = fopen('uploads/'.$filename, 'wb');
            fwrite($file, $image);
            fclose($file);
            $img_url = $request->getUri()->getBaseUrl().'/uploads/'.$filename;
        }

        // error_log(print_r("Uploaded img_url: ".$img_url));
        // echo '<br>img_url: '.$img_url;

        // Creating a new notice
        $notice = new \NoticeDetail(array(
            'title' => $data['title'],
            'body' => $data['body'],
            'img_url' => $img_url,
            'end_date' => $data['end_date'],
            'branch' => strtolower($data['branch']),
            'class' => strtolower($data['class']),
            'division' => $data['division'],
            'audience' => strtolower($data['u_type']),
            'u_id' => $u_id,
        ));
        $notice->save();
        $response->getBody()->write($notice->toJson());
    } else {
        $response->getBody()->write('{"status": 401, "error":"Unauthorized Access"}');
    }

    return $response;
});

$app->post('/register', function($request, $response, $args) {
    // header("Content-Type: application/json");

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
        $response->getBody()->write("{status: 201, message: \"User Created\"}");
    } catch (PDOException $e) {
        $response->getBody()->write("{status: 500, message: \"Username Already Exists.\"}");
    }

    return $response;
});

$app->post('/login', function($request, $response, $args) {
    header("Content-Type: application/json");

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
        $response->getBody()->write("{\"access_token\":\"" . $token['token'] . "\", \"status\": 202}");
    } else {
        $response->getBody()->write('{"status": 401, "error": "Incorrect Username/Password"}');
    }

    return $response;
});

$app->put('/changepass', function($request, $response) {
    header("Content-Type: application/json");

    // getting the user from the access_token
    $access_token = $request->getQueryParams('access_token')['access_token'];
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

            $response->getBody()->write("{\"status\": 202, \"message\": \"Password successfully changed\", \"access_token\": \"" . $token['token'] . "\"}");
        } else {
            $response->getBody()->write('{"status": 401, "error": "Incorrect Password"}');
        }
    } else {
        $response->getBody()->write('{"status": 401, "error": "Unauthorized Access"}');
    }

    return $response;
});

?>