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
     */
    public function theInboxContainsMails($numberOfMails)
    {
        $this->inboxContainsNumberOfMails((int)$numberOfMails);
    }

    /**
     * @When I follow :link in the email
     */
    public function iFollowInTheEmailSentTo($link)
    {
        $this->openMailByNumber(1);
        $this->followLinkInTheEmail($link);
    }

    /**
     * @When I clear my inbox
     */
    public function iClearMyInbox()
    {
        $this->clearInbox();
    }

}