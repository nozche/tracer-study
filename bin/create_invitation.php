<?php

require_once __DIR__ . '/../app/Services/InvitationService.php';

$options = getopt('', ['name:', 'email::', 'wa::', 'channel::']);

if (!isset($options['name'])) {
    fwrite(STDERR, "Usage: php bin/create_invitation.php --name=NAME [--email=EMAIL] [--wa=628xxxx] [--channel=email|whatsapp]\n");
    exit(1);
}

$channel = $options['channel'] ?? 'email';
$alumni = [
    'name' => $options['name'],
    'email' => $options['email'] ?? null,
    'whatsapp_number' => $options['wa'] ?? null,
];

$service = new InvitationService();
$invitation = $service->createInvitation($alumni, $channel);

echo json_encode($invitation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
