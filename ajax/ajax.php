<?php

// Include the main configuration file and a custom library file for "evaluare".
require '../../../config.php';
require_once($CFG->dirroot . '/local/evaluare/lib.php');

// Set the page context to the system context (global level).
$PAGE->set_context(context_system::instance());

// Initialize an array to store the response result.
$result = array(
    'status' => '', // Placeholder for the response status (e.g., success or error).
    'content' => '' // Placeholder for the response content or error message.
);

try {
    // Retrieve the 'action' parameter from the request, expecting it to be alphabetic.
    $action = required_param('action', PARAM_ALPHA);

    // Check if a PHP file corresponding to the action exists in the specified directory.
    if (file_exists($CFG->dirroot . '/local/evaluare/ajax/' . $action . '.php')) {
        // If the file exists, include it. This executes the file, which likely contains the action's logic.
        require $CFG->dirroot . '/local/evaluare/ajax/' . $action . '.php';
    } else {
        // If the file does not exist, throw an exception with a relevant error message.
        throw new Exception('Invalid action');
    }
} catch (Exception $ex) {
    // If an exception occurs (e.g., invalid action or other errors), handle it here.
    // Set the response status to "error" and include the exception's message in the content.
    $result['status'] = 'error';
    $result['content'] = $ex->getMessage();
}

// Set the HTTP header to indicate that the response content is in JSON format.
header('Content-Type: application/json');

// Convert the $result array to a JSON-encoded string and output it as the response.
echo json_encode($result);
