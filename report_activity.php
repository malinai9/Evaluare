<?php

// Include necessary configuration and library files
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

// Ensure the user is logged in
require_login();

// Set the context to the system level
$context = context_system::instance();
$PAGE->set_context($context);

// Set the URL for the current page
$PAGE->set_url(new moodle_url('/local/evaluare/raport_activitate.php'));

// Include a custom JavaScript file for additional functionality
$PAGE->requires->js('/local/evaluare/javascript/yearfaculty.js');

// Check if the user has the required capability to access this functionality
require_capability('moodle/category:manage', $context);

// Set the title of the page
$PAGE->set_title(get_string('report_activity', 'local_evaluare'));

// Set the heading of the page
$PAGE->set_heading(get_string('report_activity', 'local_evaluare'));

// Configure the page as an external administrative page
admin_externalpage_setup(
    'report_activity_report', // A unique identifier for this page
    get_string('report_activity', 'local_evaluare'), // The title of the report
    array('pagelayout' => 'report') // Specify the layout type as "report"
);

class report_activity_form extends moodleform
{

    // Define the form elements and structure
    function definition()
    {
        global $DB;
        $mform = $this->_form;

        // Fetch course categories to populate the year selection dropdown
        $courseCategories = $DB->get_records_sql('SELECT * FROM {course_categories} WHERE depth=1 AND visible=1 AND name LIKE "%-20%" ORDER BY id DESC');
        $years = [];

        // Loop through the categories to build the years array
        foreach ($courseCategories as $category) {
            $years[$category->id] = $category->name; // Use category ID as the key and name as the value
        }

        // Add a default "choose" option at the top of the year dropdown
        $default_year[''] = get_string('choose', 'local_evaluare');
        $years = $default_year + $years;

        // Add a dropdown element for year selection
        $mform->addElement('select', 'an_universitar', get_string('an_universitar', 'local_evaluare'), $years, array('style' => 'width: 550px;'));
        $mform->setType('an_universitar', PARAM_INT); // Set the parameter type to integer
        $mform->addRule('an_universitar', get_string('required'), 'required', null, 'client'); // Add a validation rule for required input

        // Initialize the faculty dropdown options
        $facultylist = array();
        $faculties = $DB->get_records_sql("SELECT id, name FROM {course_categories} WHERE parent!=0 AND visible=1");

        // Populate the faculty list with data from the database
        foreach ($faculties as $faculty) {
            $facultylist[$faculty->id] = $faculty->name; // Use faculty ID as key and name as value
        }

        // Add a default "all faculties" option
        $default[''] = get_string('allfaculty', 'local_evaluare');
        $facultylist = $default + $facultylist;

        // Add a dropdown element for faculty selection
        $mform->addElement('select', 'faculty', get_string('choosefaculty', 'local_evaluare'), $facultylist, array('style' => 'width: 550px;'));
        $mform->setType('faculty', PARAM_INT); // Set the parameter type to integer

        // Disable the faculty dropdown if no year is selected
        $mform->disabledIf('faculty', 'an_universitar', 'eq', '');

        // Add action buttons (e.g., "Search" and "Cancel") to the form
        $this->add_action_buttons(true, get_string('cauta', 'local_evaluare'));

        // Add custom JavaScript to enable/disable the faculty dropdown dynamically
        $this->add_custom_js();
    }

    // Add custom JavaScript for additional functionality
    private function add_custom_js()
    {
        global $PAGE;

        // Inject JavaScript code into the page
        $PAGE->requires->js_init_code("
            document.addEventListener('DOMContentLoaded', function() {
                var anUniversitarSelect = document.querySelector('select[name=\"an_universitar\"]'); // Year dropdown
                var facultySelect = document.querySelector('select[name=\"faculty\"]'); // Faculty dropdown

                // Function to enable or disable the faculty dropdown based on year selection
                function toggleFacultySelect() {
                    if (anUniversitarSelect.value === '') {
                        facultySelect.disabled = true; // Disable if no year is selected
                    } else {
                        facultySelect.disabled = false; // Enable if a year is selected
                    }
                }

                // Perform an initial check when the page loads
                toggleFacultySelect();

                // Add an event listener to update the state dynamically when the year selection changes
                anUniversitarSelect.addEventListener('change', toggleFacultySelect);
            });
        ");
    }
}


$mform = new report_activity_form();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report_activity', 'local_evaluare'));

if ($mform->is_cancelled()) {
    // Redirect to homepage if cancel button is pressed
    redirect($CFG->wwwroot . '/admin/search.php#linkmodules');
} else if ($data = $mform->get_data()) {
    // Process the submitted form data
    $an = $data->an_universitar; // Selected academic year
    $faculty = !empty($data->faculty) ? $data->faculty : null; // Selected faculty, or null if not selected

    // Fetch the records for the selected academic year and faculty (if available)
    $ancategory = $DB->get_record('course_categories', ['id' => $an]); // Academic year category details
    $facultate = $DB->get_record('course_categories', ['id' => $faculty]); // Faculty category details

    // Get the name of the academic year category
    $category_name = $DB->get_field('course_categories', 'name', array('id' => $an));

    // Check if the academic year category name follows the YYYY-YYYY format
    if (preg_match('/^\d{4}-\d{4}$/', $category_name)) {

        // Query to find courses with fewer than 3 activities/resources
        if (!empty($faculty)) {
            // SQL to find courses within the selected faculty category
            $sql = "SELECT c.id, c.fullname, COUNT(cm.id) AS activity_count
                    FROM {course} c
                    JOIN {course_categories} cc ON c.category = cc.id
                    JOIN {course_modules} cm ON cm.course = c.id
                    JOIN {modules} m ON cm.module = m.id
                    WHERE cc.path LIKE CONCAT('/%', :an_id, '/', :faculty_id, '/%')
                    AND cm.visible = 1
                    GROUP BY c.id
                    HAVING activity_count < 3";

            // Parameters for academic year and faculty IDs
            $params = ['an_id' => $an, 'faculty_id' => $faculty];
            $courses_with_few_activities = $DB->get_records_sql($sql, $params);
        } else {
            // SQL to find courses within the academic year category only
            $sql = "SELECT c.id, c.fullname, COUNT(cm.id) AS activity_count
                    FROM {course} c
                    JOIN {course_categories} cc ON c.category = cc.id
                    JOIN {course_modules} cm ON cm.course = c.id
                    JOIN {modules} m ON cm.module = m.id
                    WHERE cc.path LIKE CONCAT('/%', :an_id, '/%')
                    AND cm.visible = 1
                    GROUP BY c.id
                    HAVING activity_count < 3";

            // Parameters for the academic year ID
            $params = ['an_id' => $an];
            $courses_with_few_activities = $DB->get_records_sql($sql, $params);
        }

        // Generate the filename for the CSV report
        if (empty($faculty)) {
            $filename = 'report_activity_' . $ancategory->name . '.csv'; // Academic year only
        } else {
            $filename = 'report_activity_' . $ancategory->name . '_' . $facultate->name . '.csv'; // Academic year + faculty
        }

        // Define the file path for the temporary CSV file
        $filepath = $CFG->dataroot . '/temp/' . $filename;

        // Open the CSV file for writing
        $output = fopen($filepath, 'w');

        // Check if the file opened successfully
        if ($output === false) {
            echo $OUTPUT->notification(get_string('filecannotbecreated', 'local_evaluare'), 'error');
            return; // Exit if the file could not be created
        }

        // Write the CSV headers using a custom delimiter
        fputcsv($output, ['Faculty', 'Cours', 'Number activities', 'Teacher'], ';');

        // Populate the CSV file with data about courses and their teachers
        if (!empty($courses_with_few_activities)) {
            foreach ($courses_with_few_activities as $course) {
                // Retrieve the list of teachers for the course
                $teachers = get_course_teachers($course->id);

                // Write course data to the CSV file
                fputcsv($output, [$facultate->name, $course->fullname, $course->activity_count, $teachers], ';');
            }

            // Generate a download link for the CSV file
            $link = html_writer::link(
                new moodle_url('/local/evaluare/download.php', ['file' => $filename]),
                get_string('downloadcsv', 'local_evaluare')
            );

            // Display a success notification with the download link
            $notification_message = get_string('raportgenerat', 'local_evaluare') . ' ' . $link;
            echo $OUTPUT->notification($notification_message, 'notifysuccess');
        } else {
            // If no courses were found, add a message to the CSV
            fputcsv($output, [get_string('nofound', 'local_evaluare')], ';');
            echo $OUTPUT->notification(get_string('nofound', 'local_evaluare'), 'info');
        }

        // Close the CSV file after writing is complete
        fclose($output);
    }
}

$mform->display();

echo $OUTPUT->footer();

/**
 * Helper function to get teachers for a course
 */
function get_course_teachers($courseid)
{
    global $DB;
    $teachers = [];
    $context = context_course::instance($courseid);
    $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);

    if ($roleid) {
        // Fetch teachers for the course
        $teacher_records = get_role_users($roleid, $context, false, 'u.id, u.firstname, u.lastname');

        // Check if $teacher_records is an array
        if (is_array($teacher_records)) {
            $teachers = array_map(function ($teacher) {
                return $teacher->firstname . ' ' . $teacher->lastname;
            }, $teacher_records);
        }
    }

    // Ensure $teachers is an array before using implode
    return is_array($teachers) ? implode(', ', $teachers) : '';
}
