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

    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        parent::__construct($moduleContainer, $config);
        $this->mailHogClient = new MailHogClient();
    }

    /**
     * @param integer $numberOfMails
     */
    public function inboxContainsNumberOfMails($numberOfMails)
    {
        $this->assertEquals($numberOfMails, $this->mailHogClient->countAll());
    }

    public function clearInbox()
    {
        $this->mailHogClient->deleteAllMessages();
    }

    /**
     * @param integer $mailNumber
     */
    public function openMailByNumber($mailNumber)
    {
        $mailIndex = $mailNumber - 1;
        $this->currentMail = $this->mailHogClient->findOneByIndex($mailIndex);

        $this->assertInstanceOf(Mail::class, $this->currentMail, 'The mail with number ' . $mailNumber . ' does not exist.');
    }


    /**
     * @param string $link
     * @throws \Exception
     */
    public function followLinkInTheEmail($link)
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
     * @param string $mailBody
     * @return mixed|string
     */
    protected function parseMailBody($mailBody)
    {
        $unescapedMail = preg_replace('/(=(\r\n|\n|\r))|(?=)3D/', '', $mailBody);
        if (preg_match('/(.*)Content-Type\: text\/html/s', $unescapedMail)) {
            $unescapedMail = strip_tags($unescapedMail, '<a><img>');
        }
        return $unescapedMail;
    }

}