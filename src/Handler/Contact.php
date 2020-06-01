<?php

namespace Iwgb\OrgUk\Handler;

use Exception;
use Iwgb\OrgUk\Factory\MailgunEmailFactory;
use Iwgb\OrgUk\Psr7Utils as Psr7;
use Psr\Http\Message\ResponseInterface;
use ReCaptcha\ReCaptcha;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class Contact extends ViewHandler {

    private const EMAIL_HEADER = "New website contact from {name} {email}.\nReply to this email to contact them.\n\nMessage:\n\n";

    private const NAME_DEFAULT = 'Unknown name';

    private const EMAIL_DEFAULT = 'unknown_email@unknown.com';

    private const MESSAGE_DEFAULT = 'No message';

    private const MESSAGE_SUBJECT = 'New website contact';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, array $args): ResponseInterface {

        $data = $request->getParsedBody();

        if (
            !(
                (new ReCaptcha($this->settings['recaptcha']['secret']))
                ->verify($data['g-recaptcha-response'], $_SERVER['REMOTE_ADDR'])
                ->isSuccess()
            )
        ) {
            return Psr7::redirect($response, '/?' . http_build_query(['contactFormSent' => 'no']));
        }

        $name = htmlspecialchars($data['name'] ?? self::NAME_DEFAULT);
        $email = htmlspecialchars($data['email'] ?? self::EMAIL_DEFAULT);
        $message = htmlspecialchars($data['message'] ?? self::MESSAGE_DEFAULT);
        $target = $this->settings['contacts'][$data['target']]
            ?? $this->settings['contacts']['enquiries'];

        $header = str_replace('{name}', $name, self::EMAIL_HEADER);
        $header = str_replace('{email}', $email, $header);

        (new MailgunEmailFactory($this->settings['mailgun']))
            ->send(
                $target,
                self::MESSAGE_SUBJECT,
                $header . $message,
                $email,
            );

        return Psr7::redirect($response, '/?' . http_build_query(['contactFormSent' => 'yes']));
    }
}