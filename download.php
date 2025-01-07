<?php

require_once('../../config.php');

// Get the file parameter
$file = required_param('file', PARAM_FILE);

// Define the file path
$filepath = $CFG->dataroot . '/temp/' . $file;

// Check if the file exists
if (file_exists($filepath)) {
    // Send the file to the browser for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);

    // Optionally delete the file after download
    unlink($filepath);
    exit;
} else {
    print_error('filenotfound', 'local_evaluare');
}