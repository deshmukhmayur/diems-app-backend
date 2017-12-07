<?php

$this->group('/admin', function () {
    /**
     * The endpoints for admin operations
     */
    
    $this->get('/home', function ($request, $response) {
        $access_token = $request->getQueryParams()['access_token'];
        $token = \AccessToken::where('token', $access_token)->first();

        if ($token && $token['u_type'] == 'admin') {
            $staff_count = \StaffDetail::count();
            $student_count = \StudentDetail::count();
            $notice_admin_count = \NoticeAdminUser::count();

            return $response
                // ->withStatus(200)
                ->withJson([
                    'status' => 200,
                    'data' => [
                        'staff_count' => $staff_count,
                        'student_count' => $student_count,
                        'notice_admin_count' => $notice_admin_count
                    ]
                ]);
        }

        return $response
            // ->withStatus(401)
            ->withJson([
                'status' => 401,
                'error' => 'User not authorized'
            ]);
    });
});

$this->post('/feedback', function($request, $response) {
    $access_token = $request->getQueryParams()['access_token'];
    $token = \AccessToken::where('token', $access_token)->first();

    if ($token) {
        $data = $request->getParsedBody();

        $feedback = new \Feedback([
            'subject' => $data['subject'],
            'message' => $data['message'],
            'user_id' => $token->u_id,
            'user_type' => $token->u_type
        ]);
        $feedback->save();

        return $response
            // ->withStatus(202)
            ->withJson([
                'status' => 202,
                'message' => 'Feedback submitted successfully'
            ]);
    }

    return $response
        // ->withStatus(401)
        ->withJson([
            'status' => 401,
            'error' => 'User not authorized'
        ]);
});
