jQuery(function( $ ) {
    $('body').on('click', '.wowrestro-facebook-test-notification', function(e) {
        e.preventDefault();
        
        // Get the test message from the textarea
        var testMessage = $('.test_facebook_text').val();

        // Clear any previous error classes
        $('.test_facebook_text textarea').removeClass('error');

        if (testMessage === '') {
            // Add an error class to the textarea if it's empty
            $('.test_facebook_text').addClass('error');
        } else {
            // If the test message is not empty, proceed to send the notification

            // Replace 'RECIPIENT_USER_ID_HERE' with the actual recipient's Facebook user ID
            var recipientUserId = '6908975855847891';
            
            // var testMessage = 'Your static test message';

            var data = {
                action: 'wwro_facebook_test_notification_service', // The PHP action hook
                recipient_user_id: recipientUserId,
                test_message: testMessage
            };

            $.ajax({
                type: 'POST',
                data: data,
                url: wwrofacebookAdmin.ajax_url, // Make sure 'rpfacebookAdmin.ajaxurl' is defined in your WordPress environment
                success: function(response) {
                    if (response.success) {
                        // Notification sent successfully
                        Swal.fire({
                            icon: 'info',
                            title: wwrofacebookAdmin.message_sent,
                            text: wwrofacebookAdmin.message_sent_text
                        });
                    } else {
                        // Handle errors
                        Swal.fire({
                            icon: 'error',
                            title: wwrofacebookAdmin.message_error,
                            text: wwrofacebookAdmin.message_error_text
                        });
                    }
                    return false;
                },
                error: function(response) {
                    console.log(response)
                }
            });
        }
    });
});
