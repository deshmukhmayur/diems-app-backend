<?php

// Note To Self: All the locations are relative to public/index.php

// Include the Notice model
require '../models/Notice.php';
// Include the admin module
require '../models/Admin.php';
// Include the access token module
require '../models/AccessToken.php';


// routes for AUTHENTICATION
require '../src/routes/auth.php';
// routes for NOTICES
require '../src/routes/notices.php';

?>