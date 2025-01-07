$(document).ready(function() {
    // Check if jQuery is loaded.
    // This is a safety check to ensure the script can run correctly.
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded'); // Log an error message if jQuery is not available.
    } else {
        console.log('jQuery is loaded'); // Log a message confirming that jQuery is available.
    }

    // Attach an event listener to the HTML element with the ID 'id_an_universitar'.
    // This event triggers when the value of the element changes (e.g., a dropdown selection).
    $('#id_an_universitar').change(function() {
        // Get the selected value from the element.
        var yearId = $(this).val(); 
        console.log('Selected year ID:', yearId); // Log the selected year ID for debugging purposes.

        // Make an AJAX request to fetch faculties based on the selected year ID.
        $.ajax({
            url: '/local/evaluare/ajax/ajax.php', // The server endpoint that handles the request.
            method: 'POST', // The HTTP method used for the request (POST in this case).
            data: { 
                year_id: yearId, // Send the selected year ID as part of the request payload.
                action: 'getfaculty' // Specify the action to be performed on the server.
            },
            success: function(response) {
                // On successful response, populate the element with ID 'id_faculty'.
                // 'response.content' is expected to contain the HTML for the dropdown options.
                $('#id_faculty').html(response.content); 
            },
            error: function(xhr, status, error) {
                // Log detailed error information if the AJAX request fails.
                console.error('AJAX error:', status, error); // Log the status and error message.
                console.error('Response:', xhr.responseText); // Log the full response text for debugging.
            }
        });
    });
});
