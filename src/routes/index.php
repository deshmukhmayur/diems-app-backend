<?php

// Include the Notice model
require '../../models/Notice.php';
// Include the admin module
require '../../models/Admin.php';
// Include the access token module
require '../../models/AccessToken.php';


// routes for AUTHENTICATION
require './auth.php';
// routes for NOTICES
require './notices.php';

?>