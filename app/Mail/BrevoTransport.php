<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    protected string $apiKey;

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $senderAddress = $email->getFrom()[0] ?? null;
        $senderName = $senderAddress && $senderAddress->getName() ? $senderAddress->getName() : config('mail.from.name', 'GasGo');
        $senderEmail = $senderAddress && $senderAddress->getAddress() ? $senderAddress->getAddress() : config('mail.from.address', '23sc4114_ms@psu.edu.ph');

        $to = array_map(function (Address $address) {
            $data = ['email' => $address->getAddress()];
            if ($name = $address->getName()) {
                $data['name'] = $name;
            }
            return $data;
        }, $email->getTo());

        $payload = [
            'sender' => [
                'name' => $senderName ?: 'GasGo',
                'email' => $senderEmail,
            ],
            'to' => $to,
            'subject' => $email->getSubject() ?: 'Notification',
        ];

        if ($html = $email->getHtmlBody()) {
            $payload['htmlContent'] = is_resource($html) ? stream_get_contents($html) : (string) $html;
        }

        if ($text = $email->getTextBody()) {
            $payload['textContent'] = is_resource($text) ? stream_get_contents($text) : (string) $text;
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if (!$response->successful()) {
            throw new \Exception('Brevo API Error: ' . $response->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
