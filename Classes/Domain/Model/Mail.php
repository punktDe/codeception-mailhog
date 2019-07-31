<?php
namespace PunktDe\Codeception\Mailhog\Domain\Model;
/*
 * This file is part of the PunktDe\Codeception-Mailhog package.
 *
 * This package is open source software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Utility\Arrays;

class Mail
{

    /**
     * @var array
     */
    protected $maiLData = [];

    /**
     * @var array
     */
    protected $recipients;

    /**
     * @var string
     */
    protected $subject;
    
    /**
     * @var string
     */
    protected $body;

    
    /**
     * Mail constructor.
     * @param array $mailData
     */
    public function __construct(array $mailData)
    {
        $this->maiLData = $mailData;

        $this->body = Arrays::getValueByPath($this->maiLData, 'Content.Body');
        $this->recipients = Arrays::getValueByPath($this->maiLData, 'Content.Headers.To');
        $this->subject = Arrays::getValueByPath($this->maiLData, 'Content.Headers.Subject');

    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }
}