<?php

// Note To Self: All the locations are relative to public/index.php

// Include the Admin Users
require '../models/AdminUser.php';
// Include the Notice model
require '../models/Notice.php';
// Include the admin model
require '../models/NoticeAdmin.php';
// Include the Access Token model
require '../models/AccessToken.php';
// Include the Staff Detail model
require '../models/Staff.php';
// Include the Student Detail model
require '../models/Student.php';
// Include Class Test model
require '../models/ClassTest.php';
// Include the Subject Detail model
require '../models/Subject.php';
// Include the Department model
require '../models/DepartmentDetail.php';
// Include Class Mapping model
require '../models/ClassMapping.php';
// Include Class Teacher Mapping model
require '../models/ClassTeacherMapping.php';
// Include Mentor Batch Mapping model
require '../models/MentorBatchMapping.php';
// Include Subject Teacher Mapping model
require '../models/SubjectTeacherMapping.php';

// setting up CORS
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


// API v0.2 (support for older versions of the app)
$app->group('', function() {
    // routes for NOTICES
    require '../src/routes/notices.php';
    
    $this->any('/{route:login|register|changepass}', function($request, $response, $args) {
        return $response->withStatus(426)->withJson(array('status'=>426,
        'error'=>'Please update the app to the latest version'));
    });
});

$app->group('/api', function() {
    // routes for AUTHENTICATION module
    require '../src/routes/auth.php';
    // routes for NOTICES module
    require '../src/routes/notices.php';
    // routes for CLASS TEST module
    require '../src/routes/class_test.php';
});

?>