<?php

namespace App\Mail;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
use GuzzleHttp\Client;
use Swift_Attachment;

class ZeptoMailTransport extends Transport
{
    protected $client;
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->client = new Client();
        $this->apiKey = $apiKey;
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        // FROM (usa el del Mailable o fallback a config)
        $from = $message->getFrom();
        if (!$from) {
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');
        } else {
            $fromAddress = array_key_first($from);
            $fromName = $from[$fromAddress];
        }

        // TO, CC, BCC
        $to = $message->getTo() ?? [];
        $cc = $message->getCc() ?? [];
        $bcc = $message->getBcc() ?? [];

        $convert = function ($addresses) {
            return array_map(fn($email, $name) => [
                'email_address' => ['address' => $email, 'name' => $name],
            ], array_keys($addresses), $addresses);
        };

        // ADJUNTOS
        $attachments = [];
        foreach ($message->getChildren() as $attachment) {
            if ($attachment instanceof Swift_Attachment) {
                $attachments[] = [
                    'content' => base64_encode($attachment->getBody()),
                    'mime_type' => $attachment->getContentType(),
                    'name' => $attachment->getFilename(),
                ];
            }
        }

        // PAYLOAD
        $payload = [
            'from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
            'to' => $convert($to),
            'cc' => $convert($cc),
            'bcc' => $convert($bcc),
            'subject' => $message->getSubject(),
            'htmlbody' => $message->getBody(),
        ];

        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
        }

        // API REQUEST
        $this->client->post('https://api.zeptomail.com/v1.1/email', [
            'headers' => [
                'accept' => 'application/json',
                'authorization' => 'Zoho-enczapikey ' . $this->apiKey,
                'content-type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        return $this->numberOfRecipients($message);
    }
}
