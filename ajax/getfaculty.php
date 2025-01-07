<?php

// Retrieve optional parameters from the HTTP request with defaults and validation.
// 'year_id' is an integer parameter with a default value of 0.
$year_id = optional_param('year_id', 0, PARAM_INT);

// 'selectedFaculty' is another integer parameter with a default value of 0.
$selectedFaculty = optional_param('selected', 0, PARAM_INT);

try {
    // Initialize an empty list of faculties.
    if ($year_id) {
        // If 'year_id' is provided (non-zero), query the database to fetch faculties (course categories)
        // under the given 'year_id' (treated as a parent category).
        $faculties = $DB->get_records_sql(
            "SELECT id, name FROM {course_categories} WHERE parent=?",
            array($year_id) // Use parameterized queries to prevent SQL injection.
        );

        // Loop through the retrieved faculty records and build an associative array
        // with faculty IDs as keys and their names as values.
        foreach ($faculties as $faculty) {
            $facultylist[$faculty->id] = $faculty->name;
        }

        // Prepend an entry for "All Faculty" to the faculty list.
        $listfaculties = array('' => get_string('allfaculty', 'local_evaluare')) + $facultylist;
    } else {
        // If 'year_id' is not provided, initialize the list with only "All Faculty".
        $listfaculties = array('' => get_string('allfaculty', 'local_evaluare'));
    }

    // Prepare the HTML content for a dropdown menu.
    $content = '';
    foreach ($listfaculties as $key => $element) {
        // Check if the current faculty is the one selected in the request.
        $selected = ($selectedFaculty && $key == $selectedFaculty) ? 'selected="selected"' : '';

        // Append an `<option>` element for this faculty to the dropdown content.
        $content .= '<option value="' . $key . '" ' . $selected . '>' . $element . '</option>';
    }

    // If everything is successful, set the response status to 'ok' and include the dropdown HTML content.
    $result['status'] = 'ok';
    $result['content'] = $content;
} catch (Exception $ex) {
    // If an exception occurs, handle it gracefully by returning an error status.
    // Include the exception message in the response content for debugging purposes.
    $result['status'] = 'error';
    $result['content'] = $ex->getMessage();
}
