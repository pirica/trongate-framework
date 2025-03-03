<?php
/**
 * Trongate Mailer - A module for sending emails.
 * 
 * This class provides robust email-sending functionality using native PHP socket functions
 * to communicate with SMTP servers. It supports plain-text and HTML emails via multipart/alternative
 * MIME types, with no external dependencies. Designed for flexibility, it allows configuration
 * of SMTP providers via constants in config.php (e.g., GMAIL_* or SMTP_* prefixes).
 * 
 * Basic usage:
 * ```php
 * $mailer = new Trongate_Mailer();
 * $data = [
 *     'target_email' => 'user@example.com',
 *     'subject' => 'Welcome',
 *     'msg_plain' => 'Hello, welcome to Trongate!'
 * ];
 * $mailer->send($data); // Use the public send() method to send emails.
 * ```
 * 
 * Configuration:
 * Define providers in config.php using constants:
 * - Gmail: GMAIL_HOST, GMAIL_PORT, GMAIL_SECURE, GMAIL_USERNAME, GMAIL_PASSWORD, GMAIL_FROM_EMAIL, GMAIL_FROM_NAME (optional), GMAIL_REPLY_TO (optional), GMAIL_REPLY_TO_NAME (optional)
 * - Non-Gmail: SMTP_HOST, SMTP_PORT, SMTP_SECURE, SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM_EMAIL, SMTP_FROM_NAME (optional), SMTP_REPLY_TO (optional), SMTP_REPLY_TO_NAME (optional)
 */
class Trongate_Mailer extends Trongate {
    // SMTP settings
    private bool $smtp_debug = false;
    private int $smtp_timeout = 30;

    // Provider selection (default determined dynamically)
    private string $smtp_provider;

    // SMTP configurations (populated dynamically from constants)
    private array $smtp_configs = [];

    // Dynamic properties (set in constructor)
    private string $smtp_host;
    private int $smtp_port;
    private string $smtp_secure;
    private string $smtp_username;
    private string $smtp_password;
    private bool $smtp_auth = true;
    private string $from_email;
    private string $from_name;
    private string $reply_to;
    private string $reply_to_name;

    // State
    private $socket; // Resource or null
    private string $last_error = '';
    private string $last_response = '';

    /**
     * Constructor - Initialize with SMTP provider settings from config constants
     * 
     * Builds $smtp_configs from defined constants (GMAIL_* or SMTP_*), sets a default
     * provider if none specified, and applies the configuration. Dies if no valid provider
     * is available.
     */
    public function __construct() {
        parent::__construct();

        // Build SMTP configurations dynamically
        $this->smtp_configs = $this->build_configs();

        // Set default provider: prefer Gmail if defined, else non-Gmail, else fallback
        if (defined('GMAIL_USERNAME')) {
            $this->smtp_provider = 'gmail';
        } elseif (defined('SMTP_USERNAME')) {
            $this->smtp_provider = 'smtp';
        } else {
            $this->smtp_provider = 'default';
        }

        // Validate and apply provider config
        if (!isset($this->smtp_configs[$this->smtp_provider])) {
            die("Invalid SMTP provider: {$this->smtp_provider}. Define GMAIL_* or SMTP_* constants in config.php.");
        }

        $config = $this->smtp_configs[$this->smtp_provider];
        $this->smtp_host = $config['host'];
        $this->smtp_port = $config['port'];
        $this->smtp_secure = $config['secure'];
        $this->smtp_username = $config['username'];
        $this->smtp_password = $config['password'];
        $this->from_email = $config['from_email'];
        $this->from_name = $config['from_name'] ?? '';
        $this->reply_to = $config['reply_to'] ?? $this->from_email;
        $this->reply_to_name = $config['reply_to_name'] ?? 'No Reply';
    }

    /**
     * Build SMTP configurations from defined constants
     * 
     * Checks for GMAIL_* and SMTP_* constants to populate $smtp_configs. Includes a
     * fallback 'default' provider if no constants are defined.
     * 
     * @return array Configured SMTP providers
     */
    private function build_configs(): array {
        $configs = [];

        // Gmail provider (if constants defined)
        if (defined('GMAIL_USERNAME') && defined('GMAIL_PASSWORD') && defined('GMAIL_FROM_EMAIL')) {
            $configs['gmail'] = [
                'host' => defined('GMAIL_HOST') ? GMAIL_HOST : 'smtp.gmail.com',
                'port' => defined('GMAIL_PORT') ? (int) GMAIL_PORT : 587,
                'secure' => defined('GMAIL_SECURE') ? GMAIL_SECURE : 'tls',
                'username' => GMAIL_USERNAME,
                'password' => GMAIL_PASSWORD,
                'from_email' => GMAIL_FROM_EMAIL,
                'from_name' => defined('GMAIL_FROM_NAME') ? GMAIL_FROM_NAME : '',
                'reply_to' => defined('GMAIL_REPLY_TO') ? GMAIL_REPLY_TO : GMAIL_FROM_EMAIL,
                'reply_to_name' => defined('GMAIL_REPLY_TO_NAME') ? GMAIL_REPLY_TO_NAME : 'No Reply'
            ];
        }

        // Non-Gmail provider (if constants defined)
        if (defined('SMTP_USERNAME') && defined('SMTP_PASSWORD') && defined('SMTP_FROM_EMAIL')) {
            $configs['smtp'] = [
                'host' => defined('SMTP_HOST') ? SMTP_HOST : 'localhost',
                'port' => defined('SMTP_PORT') ? (int) SMTP_PORT : 25,
                'secure' => defined('SMTP_SECURE') ? SMTP_SECURE : '',
                'username' => SMTP_USERNAME,
                'password' => SMTP_PASSWORD,
                'from_email' => SMTP_FROM_EMAIL,
                'from_name' => defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : '',
                'reply_to' => defined('SMTP_REPLY_TO') ? SMTP_REPLY_TO : SMTP_FROM_EMAIL,
                'reply_to_name' => defined('SMTP_REPLY_TO_NAME') ? SMTP_REPLY_TO_NAME : 'No Reply'
            ];
        }

        // Fallback default if no custom config is defined
        if (empty($configs)) {
            $configs['default'] = [
                'host' => 'localhost',
                'port' => 25,
                'secure' => '',
                'username' => 'default_user',
                'password' => 'default_pass',
                'from_email' => 'no-reply@localhost',
                'from_name' => '',
                'reply_to' => 'no-reply@localhost',
                'reply_to_name' => 'No Reply'
            ];
        }

        return $configs;
    }

    /**
     * Switch SMTP provider dynamically
     * 
     * Changes the active SMTP provider and updates configuration properties.
     * 
     * @param string $provider The provider key (e.g., 'gmail', 'smtp')
     * @return bool True on success, false if provider is invalid
     */
    public function set_provider(string $provider): bool {
        if (!isset($this->smtp_configs[$provider])) {
            $this->last_error = "Invalid SMTP provider: $provider";
            return false;
        }

        $this->smtp_provider = $provider;
        $config = $this->smtp_configs[$provider];
        $this->smtp_host = $config['host'];
        $this->smtp_port = $config['port'];
        $this->smtp_secure = $config['secure'];
        $this->smtp_username = $config['username'];
        $this->smtp_password = $config['password'];
        $this->from_email = $config['from_email'];
        $this->from_name = $config['from_name'];
        $this->reply_to = $config['reply_to'];
        $this->reply_to_name = $config['reply_to_name'];
        return true;
    }

    /**
     * Send an email using the configured SMTP settings
     * 
     * Sends a plain-text or multipart/alternative email (if HTML is provided) via the SMTP server.
     * Requires recipient email, subject, and plain-text body. Logs errors and debug info if enabled.
     * 
     * Usage example:
     * ```php
     * $data = [
     *     'target_email' => 'user@example.com',
     *     'subject' => 'Welcome',
     *     'msg_plain' => 'Hello, welcome to Trongate!',
     *     'target_name' => 'User Name',              // Optional
     *     'msg_html' => '<p>Hello, <b>welcome</b>!</p>' // Optional
     * ];
     * $mailer = new Trongate_Mailer();
     * $success = $mailer->send($data);
     * ```
     * 
     * @param array $data Email details:
     *                    - target_email (string, required): Recipient's email address
     *                    - subject (string, required): Email subject line
     *                    - msg_plain (string, required): Plain-text message body
     *                    - target_name (string, optional): Recipient's name for headers
     *                    - msg_html (string, optional): HTML message body
     * @return bool True if sent successfully, false otherwise
     */
    public function send(array $data): bool {
        if (!isset($data['target_email']) || empty($data['target_email'])) {
            $this->last_error = "Target email is required";
            $this->_log_error($this->last_error);
            return false;
        }

        if (!isset($data['subject']) || empty($data['subject'])) {
            $this->last_error = "Subject is required";
            $this->_log_error($this->last_error);
            return false;
        }

        if (!isset($data['msg_plain']) || empty($data['msg_plain'])) {
            $this->last_error = "Plain-text message body is required";
            $this->_log_error($this->last_error);
            return false;
        }

        $target_name = $data['target_name'] ?? '';
        $msg_plain = $data['msg_plain'];
        $msg_html = $data['msg_html'] ?? null;

        try {
            if (!$this->_smtp_connect()) {
                return false;
            }
            
            if (!$this->_smtp_hello()) {
                return false;
            }
            
            if ($this->smtp_secure === 'tls') {
                if (!$this->_smtp_start_tls() || !$this->_smtp_hello()) {
                    return false;
                }
            }
            
            if ($this->smtp_auth && !empty($this->smtp_username) && !empty($this->smtp_password)) {
                if (!$this->_smtp_auth()) {
                    return false;
                }
            }
            
            $from = $this->_extract_email($this->from_email);
            if (!$this->_smtp_send_command("MAIL FROM:<$from>", 250)) {
                return false;
            }
            
            $to = $this->_extract_email($data['target_email']);
            if (!$this->_smtp_send_command("RCPT TO:<$to>", 250)) {
                return false;
            }
            
            if (!$this->_smtp_send_command("DATA", 354)) {
                return false;
            }
            
            $eol = "\r\n";
            $boundary = "----=" . md5(uniqid(time()));
            $headers = $this->_build_base_headers($data['target_email'], $target_name, $data['subject']);
            
            if ($msg_html) {
                $headers .= "MIME-Version: 1.0" . $eol;
                $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"" . $eol;
                $message = "--$boundary" . $eol;
                $message .= "Content-Type: text/plain; charset=UTF-8" . $eol;
                $message .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
                $message .= wordwrap($msg_plain, 70, $eol) . $eol;
                $message .= "--$boundary" . $eol;
                $message .= "Content-Type: text/html; charset=UTF-8" . $eol;
                $message .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
                $message .= $msg_html . $eol;
                $message .= "--$boundary--" . $eol;
            } else {
                $headers .= "Content-Type: text/plain; charset=UTF-8" . $eol;
                $headers .= "Content-Transfer-Encoding: 8bit" . $eol;
                $message = wordwrap($msg_plain, 70, $eol);
            }
            
            $email_content = $headers . $eol . $message . $eol . "." . $eol;
            if (!$this->_smtp_send_data($email_content)) {
                return false;
            }
            
            $this->_smtp_send_command("QUIT", 221);
            $this->_smtp_close();
            
            return true;
            
        } catch (Exception $e) {
            $this->last_error = "Unexpected error: " . $e->getMessage();
            $this->_log_error($this->last_error);
            $this->_smtp_close();
            if (ENV === 'dev') {
                echo $this->last_error;
            }
            return false;
        }
    }

    /**
     * Test method for sending a test email (development only)
     * 
     * Sends a test email and renders a detailed result view. Accessible via URL with optional
     * query parameters to override SMTP settings (e.g., ?to=user@example.com&host=...).
     * Restricted to development environment (ENV = 'dev').
     * 
     * @return void
     */
    public function test(): void {
        if (strtolower(ENV) !== 'dev') {
            echo 'This test method can only be run in development environment.';
            die();
        }

        foreach (['host', 'port', 'secure', 'username', 'password'] as $key) {
            if (isset($_GET[$key])) {
                $this->{"smtp_$key"} = ($key === 'port') ? (int) $_GET[$key] : $_GET[$key];
            }
        }

        $this->smtp_debug = true;
        $unique_id = uniqid();
        $data = [
            'target_email' => $_GET['to'] ?? 'david.webguy@gmail.com',
            'target_name' => 'David Connelly',
            'subject' => "Personal Message for David Connelly - Trongate Test [$unique_id]",
            'msg_plain' => $this->build_test_message($unique_id, 'David Connelly')
        ];

        $view_data = [
            'test_id' => $unique_id,
            'test_time' => date('Y-m-d H:i:s'),
            'target_email' => $data['target_email'],
            'subject' => $data['subject'],
            'message_preview' => $data['msg_plain'],
            'smtp_config' => [
                'host' => $this->smtp_host,
                'port' => $this->smtp_port,
                'secure' => $this->smtp_secure,
                'username' => $this->smtp_username,
                'password' => str_repeat('*', strlen($this->smtp_password)),
                'from_email' => $this->from_email,
                'from_name' => $this->from_name
            ],
            'base_url' => BASE_URL
        ];

        ob_start();
        $view_data['result'] = $this->send($data) ? 'success' : 'error';
        $view_data['smtp_log'] = ob_get_clean();
        $view_data['result_message'] = $view_data['result'] === 'success'
            ? "Test email was sent successfully to {$data['target_email']}"
            : "Failed to send test email.<br>Error: $this->last_error";
        
        if (!empty($this->last_response)) {
            $view_data['last_response'] = $this->last_response;
        }

        $this->view('trongate_mailer_test', $view_data);
    }

    /**
     * Build a test email message using a view file
     * 
     * @param string $unique_id Unique identifier for the test
     * @param string $target_name Name of the email recipient
     * @return string The rendered email message
     */
    private function build_test_message(string $unique_id, string $target_name): string {
        $data = [
            'unique_id' => $unique_id,
            'target_name' => $target_name,
            'from_email' => $this->from_email,
            'smtp_host' => $this->smtp_host,
            'test_time' => date('Y-m-d H:i:s')
        ];
        return $this->view('test_msg', $data, true);
    }

    /**
     * Build base email headers (without content-type)
     * 
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $subject Email subject
     * @return string Formatted email headers
     */
    private function _build_base_headers(string $to_email, string $to_name, string $subject): string {
        $eol = "\r\n";
        $headers = [];
        
        $headers[] = "Date: " . date('r');
        $headers[] = !empty($this->from_name) ? "From: {$this->from_name} <{$this->from_email}>" : "From: {$this->from_email}";
        $headers[] = !empty($to_name) ? "To: {$to_name} <{$to_email}>" : "To: {$to_email}";
        $headers[] = "Subject: " . $subject;
        $headers[] = !empty($this->reply_to) ? "Reply-To: {$this->reply_to}" : "Reply-To: {$this->from_email}";
        $headers[] = "Message-ID: <" . md5(uniqid(time())) . "@" . $this->_get_domain($this->from_email) . ">";
        $headers[] = "X-Mailer: Trongate Mailer";
        
        return implode($eol, $headers);
    }

    /**
     * Connect to the SMTP server
     * 
     * @return bool True if connected successfully, false otherwise
     */
    private function _smtp_connect(): bool {
        $protocol = $this->smtp_secure === 'ssl' ? 'ssl://' : '';
        $errno = 0;
        $errstr = '';
        $this->socket = @fsockopen($protocol . $this->smtp_host, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);
        
        if (!$this->socket) {
            $this->last_error = "Failed to connect to SMTP server: $errstr ($errno)";
            $this->_log_error($this->last_error);
            return false;
        }
        
        stream_set_timeout($this->socket, $this->smtp_timeout);
        $response = $this->_smtp_get_response();
        if (strpos($response, '220') !== 0) {
            $this->last_error = "SMTP server did not greet: $response";
            $this->_log_error($this->last_error);
            $this->_smtp_close();
            return false;
        }
        
        return true;
    }

    /**
     * Send HELO/EHLO command to SMTP server
     * 
     * @return bool True if successful, false otherwise
     */
    private function _smtp_hello(): bool {
        $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
        return $this->_smtp_send_command("EHLO $hostname", 250) || $this->_smtp_send_command("HELO $hostname", 250);
    }

    /**
     * Start TLS connection
     * 
     * @return bool True if successful, false otherwise
     */
    private function _smtp_start_tls(): bool {
        if (!$this->_smtp_send_command("STARTTLS", 220)) {
            return false;
        }
        
        if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $this->last_error = "Failed to enable TLS encryption";
            $this->_log_error($this->last_error);
            return false;
        }
        
        return true;
    }

    /**
     * Authenticate with SMTP server using LOGIN method
     * 
     * @return bool True if successful, false otherwise
     */
    private function _smtp_auth(): bool {
        if (!$this->_smtp_send_command("AUTH LOGIN", 334)) {
            return false;
        }
        
        if (!$this->_smtp_send_command(base64_encode($this->smtp_username), 334)) {
            return false;
        }
        
        if (!$this->_smtp_send_command(base64_encode($this->smtp_password), 235)) {
            return false;
        }
        
        return true;
    }

    /**
     * Send a command to the SMTP server and verify response
     * 
     * @param string $command Command to send
     * @param int $expected_code Expected SMTP response code
     * @return bool True if command was successful, false otherwise
     */
    private function _smtp_send_command(string $command, int $expected_code): bool {
        if (!$this->socket) {
            $this->last_error = "No connection to SMTP server";
            $this->_log_error($this->last_error);
            return false;
        }
        
        $this->_debug_print("CLIENT: $command");
        fwrite($this->socket, $command . "\r\n");
        $response = $this->_smtp_get_response();
        $code = (int) substr($response, 0, 3);
        
        if ($code !== $expected_code) {
            $this->last_error = "SMTP Error: $response (Expected code: $expected_code)";
            $this->_log_error($this->last_error);
            return false;
        }
        
        return true;
    }

    /**
     * Send email data to the SMTP server
     * 
     * @param string $data Email content (headers + body)
     * @return bool True if data was accepted, false otherwise
     */
    private function _smtp_send_data(string $data): bool {
        if (!$this->socket) {
            $this->last_error = "No connection to SMTP server";
            $this->_log_error($this->last_error);
            return false;
        }
        
        $this->_debug_print("CLIENT: [EMAIL DATA]");
        fwrite($this->socket, $data);
        $response = $this->_smtp_get_response();
        $code = (int) substr($response, 0, 3);
        
        if ($code !== 250) {
            $this->last_error = "SMTP Error: $response (Email data not accepted)";
            $this->_log_error($this->last_error);
            return false;
        }
        
        return true;
    }

    /**
     * Get response from SMTP server
     * 
     * @return string Server response (empty if no connection)
     */
    private function _smtp_get_response(): string {
        if (!$this->socket) {
            return '';
        }
        
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            $this->_debug_print("SERVER: $line");
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        
        $this->last_response = $response;
        return $response;
    }

    /**
     * Close the SMTP connection
     * 
     * @return void
     */
    private function _smtp_close(): void {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    /**
     * Extract email address from a string that might include a name
     * 
     * @param string $address Email address potentially with name
     * @return string Pure email address
     */
    private function _extract_email(string $address): string {
        if (preg_match('/\<(.*?)\>/', $address, $matches)) {
            return $matches[1];
        }
        return $address;
    }

    /**
     * Extract domain from email address
     * 
     * @param string $email Email address
     * @return string Domain part of the email
     */
    private function _get_domain(string $email): string {
        $parts = explode('@', $email);
        return $parts[1] ?? 'localhost';
    }

    /**
     * Convert HTML to plain text with support for common formatting
     * 
     * Transforms HTML content into readable plain text, preserving structure where possible
     * (e.g., paragraphs, lists, links). Designed for email bodies, ensuring SMTP compatibility.
     * 
     * @param string $html HTML content to convert
     * @return string Plain-text representation of the HTML
     */
    public function _html_to_plain_text(string $html): string {
        $text = html_entity_decode(str_replace(["\r\n", "\n", "\r"], ' ', $html), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/<(pre|code)>(.*?)<\/\1>/is', "\r\n```\r\n$2\r\n```\r\n", $text);
        $replacements = [
            '/<br\s*\/?>/i' => "\r\n",
            '/<\/?(p|div)>/i' => "\r\n\r\n",
            '/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i' => "**$1**\r\n",
            '/<li[^>]*>(.*?)<\/li>/i' => "* $1\r\n",
            '/<a\s+href=["\'](.*?)["\'][^>]*>(.*?)<\/a>/i' => "$2 ($1)",
        ];
        $text = preg_replace(array_keys($replacements), array_values($replacements), $text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace("/(\r\n\s*)+/", "\r\n", $text);
        $text = trim($text);
        return wordwrap($text, 70, "\r\n");
    }

    /**
     * Load SMTP configurations from framework config
     * 
     * Overrides default $smtp_configs if defined in config.php
     * 
     * @return void
     */
    public function load_config(): void {
        if (isset($this->config['smtp_configs']) && is_array($this->config['smtp_configs'])) {
            $this->smtp_configs = $this->config['smtp_configs'];
            $this->set_provider($this->smtp_provider);
        }
    }

    /**
     * Log an error message to error_log and debug output
     * 
     * @param string $message Error message to log
     * @return void
     */
    private function _log_error(string $message): void {
        error_log("Trongate Mailer Error: " . $message);
        if ($this->smtp_debug) {
            echo "Trongate Mailer Error: " . $message . "<br>";
        }
    }

    /**
     * Print debug message if debug mode is enabled
     * 
     * @param string $message Debug message
     * @return void
     */
    private function _debug_print(string $message): void {
        if ($this->smtp_debug) {
            echo htmlspecialchars($message) . "<br>";
        }
    }
}