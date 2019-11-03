<?php

namespace PunktDe\Codeception\Mailhog\Module;

/*
 * This file is part of the PunktDe\Codeception-Mailhog package.
 *
 * This package is open source software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use PunktDe\Codeception\Mailhog\Domain\MailHogClient;
use PunktDe\Codeception\Mailhog\Domain\Model\Mail;


class Mailhog extends Module
{

    /**
     * @var MailHogClient
     */
    protected $mailHogClient;

    /**
     * @var Mail
     */
    protected $currentMail = null;

    /**
     * Mailhog constructor.
     * @param ModuleContainer $moduleContainer
     * @param mixed[]|null $config
     */
    public function __construct(ModuleContainer $moduleContainer, array $config = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->mailHogClient = new MailHogClient($config['base_uri'] ?? null);
    }

    /**
     * @param int $numberOfMails
     * @throws \Exception
     */
    public function inboxContainsNumberOfMails(int $numberOfMails): void
    {
        $this->assertEquals($numberOfMails, $this->mailHogClient->countAll());
    }

    public function clearInbox(): void
    {
        $this->mailHogClient->deleteAllMessages();
    }

    /**
     * @param int $mailNumber
     */
    public function openMailByNumber(int $mailNumber): void
    {
        $mailIndex = $mailNumber - 1;
        $this->currentMail = $this->mailHogClient->findOneByIndex($mailIndex);

        $this->assertInstanceOf(Mail::class, $this->currentMail, 'The mail with number ' . $mailNumber . ' does not exist.');
    }


    /**
     * @param string $link
     * @throws \Exception
     */
    public function followLinkInTheEmail(string $link): void
    {
        $mail = $this->parseMailBody($this->currentMail->getBody());
        if (preg_match('/(http[^\s|^"]*' . preg_quote($link, '/') . '[^\s|^"]*)/', $mail, $links)) {
            $webdriver = $this->getModule('WebDriver'); /** @var Module\WebDriver $webdriver */
            $targetLink = $links[0];
            $targetLink = urldecode($targetLink);
            $targetLink = html_entity_decode($targetLink);
            $webdriver->amOnUrl($targetLink);
            return;
        }
        throw new \Exception(sprintf('Did not find the link "%s" in the mail', $link));
    }

    /**
     * @param string $text
     * @throws \Exception
     */
    public function seeTextInMail(string $text): void
    {
        $mail = $this->parseMailBody($this->currentMail->getBody());
        if (stristr($mail, $text)) {
            return;
        }
        throw new \Exception(sprintf('Did not find the text "%s" in the mail', $text));
    }

    /**
     * @param string $address
     * @throws \Exception
     */
    public function checkRecipientAddress(string $address): void
    {
        $recipients = $this->currentMail->getRecipients();
        foreach ($recipients as $recipient) {
            if ($recipient === $address) {
                return;
            }
        }
        throw new \Exception(sprintf('Did not find the recipient "%s" in the mail', $address));
    }

    /**
     * @throws \Exception
     */
    public function checkIfSpam(): void
    {
        $subjectArray = $this->currentMail->getSubject();

        foreach ($subjectArray as $subject) {
            if (strpos($subject, "[SPAM]") === 0) {
                return;
            }
        }

        throw new \Exception(sprintf('Could not find [SPAM] at the beginning of subject "%s"', $subject));
    }

    /**
     * @param string $mailBody
     * @return string
     */
    protected function parseMailBody(string $mailBody): string
    {
        $unescapedMail = preg_replace('/(=(\r\n|\n|\r))|(?=)3D/', '', $mailBody);
        if (preg_match('/(.*)Content-Type\: text\/html/s', $unescapedMail)) {
            $unescapedMail = strip_tags($unescapedMail, '<a><img>');
        }
        return $unescapedMail;
    }

}
