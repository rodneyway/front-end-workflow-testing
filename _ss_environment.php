<?php
// Set the $_FILE_MAPPING for running the test cases, it's basically a fake but useful
global $_FILE_TO_URL_MAPPING;
$_FILE_TO_URL_MAPPING[dirname(__FILE__)] = 'http://localhost';