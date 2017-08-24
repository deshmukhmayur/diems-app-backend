<?php

$app->get('/notices', function($request, $response, $args) {
    // header("Content-Type: application/json");
    
    // Fetch all notices
    $notices = \NoticeDetail::orderBy('created_at', 'desc')->get();
    return $response->withJson($notices);
});

$app->post('/notices', function($request, $response, $args) {
    // header("Content-Type: application/json");
    
    // getting the user from the access_token
    $access_token = $request->getQueryParams()['access_token'];
    $user = \AccessToken::where('token', $access_token)->get()[0];

    if ($user) {
        // getting the request body
        $json = $request->getBody();
        $data = json_decode($json, true);

        // fetching the u_id of the current user
        $u_id = \AdminUser::where('username', $user['username'])->first()['id'];
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
        return $response->withJson($notice);
    } else {
        $response->getBody()->write('{"status": 401, "error":"Unauthorized Access"}');
        return $response->withJson(array('status'=>401,
                                        'error'=>'Unauthorized Access'));
    }
});

$app->delete('/notices/{n_id}', function($request, $response, $args) {
    // header("Content-Type: application/json");

    $access_token = $request->getQueryParams()['access_token'];
    // echo $access_token;
    $user = \AccessToken::where('token', $access_token)->first();
    // echo $user;

    if ($user) {
        $n_id = $request->getAttribute('n_id');
        $notice = \NoticeDetail::find($n_id);
        // echo $notice->toJson();
        $u_id = \AdminUser::where('username', $user['username'])->first()['id'];
        if (!$notice) {
            return $response->withJson(array('status'=>400,
                                            'error'=>'Notice not found'));
        } else {
            if ($notice['u_id'] == $u_id) {
                $img = pathinfo($notice['img_url'], PATHINFO_BASENAME);
                $notice->delete();
                // delete the image
                unlink("uploads/$img");

                return $response->withJson(array('status'=>200,
                                                'message'=>'Notice Deleted'));
            }
        }
    }
    return $response->withJson(array('status'=>401,
                                    'error'=>'User not authorized'));
});

$app->get('/self/notices', function($request, $response, $args) {
    // header("Content-Type: application/json");
    
    $access_token = $request->getQueryParams()['access_token'];
    // echo $access_token;
    $user = \AccessToken::where('token', $access_token)->first();

    if ($user) {
        // fetching the u_id of the current user
        $u_id = \AdminUser::where('username', $user['username'])->first()['id'];

        // Fetch all notices
        $notices = \NoticeDetail::where('u_id', $u_id)
                                // ->orderBy('end_date', 'asc')
                                ->orderBy('created_at', 'asc')
                                ->get();
        return $response->withJson($notices);
    } else {
        return $response->withJson(array('status'=>401,
                                        'error'=>'Unauthorized Access'));
    }
});

?>