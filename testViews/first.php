<?php

http_response_code(201);
header('Content-Type: text/html');
header('X-Test-Header: MyTest');
include dirname(__FILE__) . '/html/content.html';
