<?php

namespace Iwgb\OrgUk\Factory;

use Mailgun\Mailgun;
use Mailgun\Model\Message\SendResponse;

class MailgunEmailFactory {

    private Mailgun $mailgun;

    private string $domain;

    private string $from;

    public function __construct(array $settings) {
        $this->mailgun = Mailgun::create("key-{$settings['key']}");
        $this->domain = $settings['domain'];
        $this->from = $settings['from'];
        return $this;
    }

    public function send(string $to, string $subject, string $text, string $replyTo): SendResponse {
        return $this->mailgun->messages()->send($this->domain, [
            'to'        => $to,
            'from'      => $this->from,
            'subject'   => $subject,
            'text'      => $text,
            'h:Reply-To'=> $replyTo,
        ]);
    }
}