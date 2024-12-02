<?php
namespace PunktDe\Codeception\Mailhog\ActorTraits;

/*
 * This file is part of the PunktDe\Codeception-Mailhog package.
 *
 * This package is open source software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */
trait Mailhog {

    /**
     * @Then /^the inbox contains (\d+) mails?$/
     * @param int $numberOfMails
     */
    public function theInboxContainsMails(string $numberOfMails): void
    {
        $this->inboxContainsNumberOfMails((int)$numberOfMails);
    }

    /**
     * @When I follow :link in the email
     * @param string $link
     */
    public function iFollowInTheEmailSentTo(string $link): void
    {
        $this->openMailByNumber(1);
        $this->followLinkInTheEmail($link);
    }

    /**
     * @When I clear my inbox
     */
    public function iClearMyInbox(): void
    {
        $this->clearInbox();
    }

    /**
     * @When I open the first mail
     */
    public function iOpenTheFirstMail(): void
    {
        $this->openMailByNumber(1);
    }

    /**
     * @When I open the second mail
     */
    public function iOpenTheSecondMail(): void
    {
        $this->openMailByNumber(2);
    }

    /**
     * @Then I should see :text in the email
     * @Then I should see :text in the email with decodeQP :decodeQuotedPrintableFlag
     * @param string $text
     * @param bool $decodeQuotedPrintableFlag
     */
    public function iSeeInMail(string $text, string|bool $decodeQuotedPrintableFlag = false): void
    {
        $this->seeTextInMail($text, $decodeQuotedPrintableFlag);
    }

    /**
     * @Then The email is addressed to :address
     * @param string $address
     */
    public function mailIsAddressedTo(string $address): void
    {
        $this->checkRecipientAddress($address);
    }

    /**
     * @Then I should see :subject in the email subject
     * @Then I should see :subject in the email subject with decodeQP :mimeDecodeFlag
     * @Then I should see :subject in the email subject with decodeQP :mimeDecodeFlag and charset :charset
     * @param string $subject
     * @param bool $mimeDecodeFlag
     */
    public function iSeeSubjectOfMail(string $subject, string|bool $mimeDecodeFlag = false, string $charset='UTF-8'): void
    {
        $this->seeSubjectOfMail($subject, $mimeDecodeFlag, $charset);
    }

    /**
     * @Then This mail is spam
     */
    public function ifSpamMail(): void
    {
        $this->checkIfSpam();
    }

}
