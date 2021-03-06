<?php 

// Middleware for deleting expired notices
$this->add(function($req, $res, $next) {
    /* not very efficient, but it works.
    */
    $expired = \NoticeDetail::where('end_date', '<', date('Y-m-d H:i:s'));
    $expired->update([
        'expired' => true
    ]);
    $expired->delete();
    $response = $next($req, $res);
    return $response;
});

$this->group('/notices', function() {
    // GET all the notices
    $this->get('', function($request, $response, $args) {
        // Fetch all notices
        $notices = \NoticeDetail::latest()->get();
        return $response->withJson($notices);
    });
    
    // POST a new notice
    $this->post('', function($request, $response, $args) {
        // getting the user from the access_token
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'notice-admin') {
            // getting the request body
            $json = $request->getBody();
            $data = json_decode($json, true);
    
            // fetching the u_id of the current user
            $user = \NoticeAdminUser::find($token['u_id']);
    
            // error_log(print_r("Response: \n" . $data['end_date']), 4);
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
                'u_id' => $user['id'],
            ));
            $notice->save();
            return $response->withJson($notice);
        } else {
            return $response->withJson(array('status'=>401,
                                            'error'=>'Unauthorized Access'));
        }
    });
    
    // DELETE a notice
    $this->delete('/{n_id}', function($request, $response, $args) {
    
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();
        // echo $token;
    
        if ($token && $token['u_type'] == 'notice-admin') {
            $user = \NoticeAdminUser::find($token['u_id']);
            $notice = \NoticeDetail::find($args['n_id']);
    
            if (!$notice) {
                return $response->withJson(array('status'=>400,
                                                'error'=>'Notice not found'));
            } else {
                if ($notice['u_id'] == $user['id']) {
                    if ($notice['img_url']) {
                        $img = pathinfo($notice['img_url'], PATHINFO_BASENAME);
                        // delete the image
                        unlink("uploads/$img");
                    }
                    $notice->delete();
    
                    return $response->withJson(array('status'=>204,
                                                    'message'=>'Notice Deleted'));
                }
            }
        }
        return $response->withJson(array('status'=>401,
                                        'error'=>'User not authorized'));
    });
    
    // GET the notices of the user
    $this->get('/self', function($request, $response, $args) {
        $access_token = $request->getQueryParams()['access_token'];
        // echo $access_token;
        $token = \AccessToken::where('token', $access_token)->first();
    
        if ($token && $token['u_type'] == 'notice-admin') {
            // fetching the u_id of the current user
            $user = \NoticeAdminUser::find($token['u_id']);
    
            // Fetch all notices
            $notices = \NoticeDetail::where('u_id', $user['id'])
                                    // ->orderBy('end_date', 'asc')
                                    ->orderBy('created_at', 'asc')
                                    ->get();
            return $response->withJson($notices);
        } else {
            return $response->withJson(array('status'=>401,
                                            'error'=>'Unauthorized Access'));
        }
    });

    // GET the trashed notices
    $this->get('/self/trashed', function($request, $response) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();

        if ($token && $token['u_type'] == 'notice-admin') {
            $user = \NoticeAdminUser::find($token->u_id);

            // fetch the notices trashed by the $user
            $notice = \NoticeDetail::onlyTrashed()
                                    ->orderBy('end_date', 'desc')
                                    ->get();

            return $response->withJson($notice);
        }

        return $response->withJson(array(
            'status' => 401,
            'error' => 'Unauthorized'
        ));
    });
});

?>
