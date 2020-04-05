<?php

namespace Iwgb\OrgUk\Handler;

use Exception;
use Iwgb\OrgUk\Factory\MailgunEmailFactory;
use ReCaptcha\ReCaptcha;
use Siler\Http\Request;
use Siler\Http\Response;
use voku\helper\UTF8;

class Contact extends RootHandler {

    private const EMAIL_HEADER = "New website contact from {name} {email}.\nReply to this email to contact them.\n\nMessage:\n\n";

    private const NAME_DEFAULT = 'Unknown name';

    private const EMAIL_DEFAULT = 'unknown_email@unknown.com';

    private const MESSAGE_DEFAULT = 'No message';

    private const MESSAGE_SUBJECT = 'New website contact';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(array $routeParams): void {

        $data = Request\post();

        if (
            !(new ReCaptcha($this->settings['recaptcha']['secret']))
            ->verify($data['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'])
            ->isSuccess()
        ) {
            Response\redirect('/?contactFormSent=no');
            return;
        }

        $name = htmlspecialchars($data['name'] ?? self::NAME_DEFAULT);
        $email = htmlspecialchars($data['email'] ?? self::EMAIL_DEFAULT);
        $message = htmlspecialchars($data['message'] ?? self::MESSAGE_DEFAULT);
        $target = $this->settings['contacts'][$data['target']]
            ?? $this->settings['contacts']['enquiries'];

        $header = UTF8::str_replace('{name}', $name, self::EMAIL_HEADER);
        $header = UTF8::str_replace('{email}', $email, $header);

        (new MailgunEmailFactory($this->settings['mailgun']))->send(
            $target,
            self::MESSAGE_SUBJECT,
            $header . $message,
            $email
        );

        Response\redirect('/?contactFormSent=yes');
    }
}