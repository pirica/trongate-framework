<!DOCTYPE html>
<html>
<head>
    <title>Trongate Mailer Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; overflow: auto; }
        .success { background-color: #dff0d8; color: #3c763d; padding: 15px; border: 1px solid #d6e9c6; border-radius: 4px; margin-top: 20px; }
        .error { background-color: #f2dede; color: #a94442; padding: 15px; border: 1px solid #ebccd1; border-radius: 4px; margin-top: 20px; }
        .info-box { background-color: #d9edf7; color: #31708f; padding: 15px; border: 1px solid #bce8f1; border-radius: 4px; margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .alternative-test { margin-top: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .message-preview { background-color: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px; white-space: pre-line; }
    </style>
</head>
<body>
    <h1>Trongate Mailer Test</h1>

    <?php if (isset($output)): ?>
        <p><?= out($output) ?></p>
    <?php else: ?>
        <div class="info-box">
            <h3>Test Information</h3>
            <p><strong>Test ID:</strong> <?= out($test_id) ?></p>
            <p><strong>Time:</strong> <?= out($test_time) ?></p>
            <p><strong>Target Email:</strong> <?= out($target_email) ?></p>
            <p><strong>Subject:</strong> <?= out($subject) ?></p>
        </div>

        <h3>Message Preview</h3>
        <div class="message-preview"><?= nl2br(out($message_preview)) ?></div>

        <h3>SMTP Configuration</h3>
        <table>
            <tr><th>Setting</th><th>Value</th></tr>
            <tr><td>SMTP Host</td><td><?= out($smtp_config['host']) ?></td></tr>
            <tr><td>SMTP Port</td><td><?= out($smtp_config['port']) ?></td></tr>
            <tr><td>Security</td><td><?= out($smtp_config['secure']) ?></td></tr>
            <tr><td>Username</td><td><?= out($smtp_config['username']) ?></td></tr>
            <tr><td>Password</td><td><?= out($smtp_config['password']) ?></td></tr>
            <tr><td>From Email</td><td><?= out($smtp_config['from_email']) ?></td></tr>
            <tr><td>From Name</td><td><?= out($smtp_config['from_name']) ?></td></tr>
        </table>

        <hr>
        <h3>SMTP Communication Log</h3>
        <pre><?= out($smtp_log) ?></pre>

        <h2>Test Result</h2>
        <div class="<?= out($result) ?>">
            <strong><?= ucfirst(out($result)) ?>!</strong> <?= $result_message ?>
            <?php if ($result === 'success'): ?>
                <p>The email may take a few minutes to arrive. Please check your inbox (and spam folder) for an email with subject: "<?= out($subject) ?>"</p>
                <p>If the email doesn't arrive within 5 minutes, try the following:</p>
                <ol>
                    <li>Check your spam/junk folder</li>
                    <li>Try sending to an alternative email address (use the form below)</li>
                    <li>Try using alternative SMTP settings (see form below)</li>
                    <li>Check if your domain has proper SPF, DKIM, and DMARC records</li>
                </ol>
            <?php endif; ?>
        </div>

        <?php if (isset($last_response)): ?>
            <h3>Last Server Response</h3>
            <pre><?= out($last_response) ?></pre>
        <?php endif; ?>

        <div class="alternative-test">
            <h3>Test with Alternative Settings</h3>
            <p>Use this form to test with different settings:</p>
            <form method="get" action="<?= out($base_url) ?>trongate_mailer/test">
                <table>
                    <tr>
                        <td>SMTP Host:</td>
                        <td><input type="text" name="host" value="smtp.gmail.com" style="width: 100%"></td>
                    </tr>
                    <tr>
                        <td>SMTP Port:</td>
                        <td><input type="text" name="port" value="587" style="width: 100%"></td>
                    </tr>
                    <tr>
                        <td>Security:</td>
                        <td>
                            <select name="secure" style="width: 100%">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                                <option value="">None</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" name="username" placeholder="your-email@gmail.com" style="width: 100%"></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="password" style="width: 100%"></td>
                    </tr>
                    <tr>
                        <td>Send to:</td>
                        <td><input type="email" name="to" placeholder="alternative@email.com" style="width: 100%"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" value="Test with These Settings"></td>
                    </tr>
                </table>
            </form>
            <p><strong>Note for Gmail:</strong> If using Gmail, you'll need to use an <a href="https://support.google.com/accounts/answer/185833" target="_blank">App Password</a> instead of your regular password.</p>
        </div>

        <div class="info-box">
            <h3>Email Deliverability Tips</h3>
            <p>To improve email deliverability, ensure your domain has proper DNS records:</p>
            <ul>
                <li><strong>SPF Record</strong>: Specifies which servers are allowed to send email from your domain</li>
                <li><strong>DKIM</strong>: Allows receiving servers to verify your emails haven't been tampered with</li>
                <li><strong>DMARC</strong>: Tells receiving servers what to do with emails that fail SPF or DKIM checks</li>
            </ul>
            <p>You can check your domain's DNS records using tools like <a href="https://mxtoolbox.com/" target="_blank">MXToolbox</a>.</p>
        </div>
    <?php endif; ?>
</body>
</html>