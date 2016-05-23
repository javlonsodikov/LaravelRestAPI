<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) {
    $err = 0;
    $data = [];
    foreach ($exception->getTrace() as $error) {
        if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) {
            $data[$err] = $error;
        }
    }
    echo json_encode(array_merge([
        "success"  => false,
        "error"    => "An uncaught Exception was encountered",
        "Type"     => get_class($exception),
        "Message"  => $message,
        "Filename" => $exception->getLine(),
    ], $data));
} else {
    echo json_encode(["success" => false, "error" => "Something went wrong", "message" => $message]);
    //TODO:collect all error info and send to administrator
}
