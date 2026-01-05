<?php

class NotificationSender
{
    public function sendEmail(string $email, string $subject, string $body): array
    {
        // Stub: Replace with mail gateway integration (e.g., SMTP/API client)
        // Return structure includes success flag and metadata for logging.
        return [
            'success' => true,
            'metadata' => [
                'transport' => 'email',
                'recipient' => $email,
                'subject' => $subject,
                'body_preview' => mb_substr($body, 0, 120),
            ],
        ];
    }

    public function sendWhatsApp(string $number, string $body): array
    {
        // Stub: Replace with WhatsApp API integration
        return [
            'success' => true,
            'metadata' => [
                'transport' => 'whatsapp',
                'recipient' => $number,
                'body_preview' => mb_substr($body, 0, 120),
            ],
        ];
    }
}
