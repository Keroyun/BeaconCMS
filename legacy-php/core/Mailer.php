<?php
declare(strict_types=1);

/**
 * Mailer
 * Lightweight SMTP client with no dependencies (replaces WP Mail SMTP)
 */
class Mailer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $encryption;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $setting = new Setting();
        $this->host = $setting->get('smtp_host', '');
        $this->port = (int)$setting->get('smtp_port', 587);
        $this->username = $setting->get('smtp_username', '');
        $this->password = $setting->get('smtp_password', '');
        $this->encryption = $setting->get('smtp_encryption', 'tls'); // tls, ssl, none
        $this->fromEmail = $setting->get('smtp_from_email', 'noreply@beaconcms.local');
        $this->fromName = $setting->get('smtp_from_name', 'BeaconCMS');
    }

    /**
     * Send an email via SMTP
     */
    public function send(string $to, string $subject, string $htmlBody): bool
    {
        if (empty($this->host)) {
            // Fallback to PHP mail() if SMTP not configured
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            return mail($to, $subject, $htmlBody, $headers);
        }

        $transport = ($this->encryption === 'ssl') ? 'ssl://' . $this->host : $this->host;
        $socket = stream_socket_client($transport . ':' . $this->port, $errno, $errstr, 15);
        if (!$socket) {
            error_log("SMTP Connect Error: $errstr ($errno)");
            return false;
        }

        $this->read($socket); // read greeting

        $this->command($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'));

        if ($this->encryption === 'tls') {
            $this->command($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $this->command($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        }

        if (!empty($this->username)) {
            $this->command($socket, "AUTH LOGIN");
            $this->command($socket, base64_encode($this->username));
            $this->command($socket, base64_encode($this->password));
        }

        $this->command($socket, "MAIL FROM: <{$this->fromEmail}>");
        $this->command($socket, "RCPT TO: <$to>");
        $this->command($socket, "DATA");

        // Construct email
        $boundary = md5(uniqid((string)time()));
        $message = "Date: " . date("r") . "\r\n";
        $message .= "To: $to\r\n";
        $message .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $message .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "\r\n";
        $message .= $htmlBody . "\r\n";
        $message .= ".";

        $this->command($socket, $message);
        $this->command($socket, "QUIT");
        
        fclose($socket);
        return true;
    }

    private function command($socket, $cmd): string
    {
        fwrite($socket, $cmd . "\r\n");
        return $this->read($socket);
    }

    private function read($socket): string
    {
        $res = '';
        while ($str = fgets($socket, 515)) {
            $res .= $str;
            if (substr($str, 3, 1) == ' ') {
                break;
            }
        }
        return $res;
    }
}
