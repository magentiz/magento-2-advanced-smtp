<?php
/**
 * Copyright © Open Techiz. All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magentiz\AdvancedSmtp\Api;

use Laminas\Mail\Message;

interface MailServiceInterface
{
    /**
     * Send a message
     *
     * @param  Message $message
     * @return mixed
     */
    public function send(Message $message);
}
