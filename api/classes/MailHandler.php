<?php

class MailHandler {
    public static function sendEmail($email, $letterHeadPath) {
        $to = $email;
        $subject = "New User Registration";
        $message = "A new user has registered.";
        $headers = "From: no-reply@yourdomain.com";

        $file = file_get_contents($letterHeadPath);
        $boundary = md5(rand());

        $headers .= "\r\nMIME-Version: 1.0\r\n" .
                    "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"";

        $body = "--" . $boundary . "\r\n" .
                "Content-Type: text/plain; charset=ISO-8859-1\r\n" .
                "Content-Transfer-Encoding: 7bit\r\n" .
                "\r\n" .
                $message . "\r\n" .
                "--" . $boundary . "\r\n" .
                "Content-Type: application/pdf; name=\"" . basename($letterHeadPath) . "\"\r\n" .
                "Content-Transfer-Encoding: base64\r\n" .
                "Content-Disposition: attachment; filename=\"" . basename($letterHeadPath) . "\"\r\n" .
                "\r\n" .
                chunk_split(base64_encode($file)) . "\r\n" .
                "--" . $boundary . "--";

        mail($to, $subject, $body, $headers);
    }
}
