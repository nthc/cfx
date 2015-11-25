<?php
if(isset($_COOKIE['CFX_TEST_ID'])){
    $data = xdebug_get_code_coverage();
    xdebug_stop_code_coverage();
    file_put_contents("coverage_data/{$_COOKIE['CFX_TEST_ID']}." . uniqid() , serialize($data));
}