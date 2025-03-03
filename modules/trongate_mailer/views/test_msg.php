Dear <?= out($target_name) ?>,

Thank you for testing the Trongate SMTP mailer. This email is specifically addressed to you as part of your development process.

This is confirmation that the email system is working correctly. Below are some details that may help identify this specific test:

* Test ID: <?= out($unique_id) ?>
* Date and Time: <?= out($test_time) ?>
* Sent from: <?= out($from_email) ?>
* Sent via: <?= out($smtp_host) ?>

If you're reading this message, it means the email delivery was successful!

Best regards,
The Trongate Framework Team