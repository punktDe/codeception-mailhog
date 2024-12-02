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

        $this->assertInstanceOf(
            Mail::class,
            $this->currentMail,
            'The mail with number ' . $mailNumber . ' does not exist.'
        );
    }


    /**
     * @param string $link
     * @throws \Exception
     */
    public function followLinkInTheEmail(string $link): void
    {
        $mail = $this->parseMailBody($this->currentMail->getBody());
        if (preg_match('/(http[^\s|^"]*' . preg_quote($link, '/') . '[^\s|^"]*)/', $mail, $links)) {
            $webdriver = $this->getModule('WebDriver');
            /** @var Module\WebDriver $webdriver */
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
     * @param bool $quotedPrintableDecodeFlag
     * @throws \Exception
     */
    public function seeTextInMail(string $text, bool $quotedPrintableDecodeFlag = false): void
    {
        $mail = $this->parseMailBody($this->currentMail->getBody());
        if (stristr(self::quotedPrintableDecodeRespectingEquals($mail, $quotedPrintableDecodeFlag), $text)) {
            return;
        }
        throw new \Exception(sprintf('Did not find the text "%s" in the mail %s', $text));
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
     * @param string $text
     * @param bool $mimeDecodeFlag
     * @param string $charset
     * @throws \Exception
     */
    public function seeSubjectOfMail(string $text, bool $mimeDecodeFlag = false, string $charset = 'UTF-8'): void
    {
        $subjectArray = $this->currentMail->getSubject();

        foreach ($subjectArray as $subject) {
            if (stristr(self::mimeDecodeSubject($subject, $charset), $text)) {
                return;
            }
        }
        throw new \Exception(sprintf('Did not find the subject "%s" in the mail', $text));
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


    /**
     * @param string $string
     * @param bool $quotedPrintableDecodeFlag
     * @return string
     */
    static protected function quotedPrintableDecodeRespectingEquals(
        string $string,
        bool $quotedPrintableDecodeFlag = false
    ): string {
        if ($quotedPrintableDecodeFlag) {
            return quoted_printable_decode($string);
        }

        return quoted_printable_decode(str_replace('=', '=3D', $string));
    }


    /**
     * @param string $string
     * @param string $charset
     * @return string
     */
    static protected function mimeDecodeSubject(string $string, string $charset): string
    {
        return iconv_mime_decode($string, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $charset);
    }
}
