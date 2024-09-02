<?php
/*
* File: Issue355Test.php
* Category: -
* Author: M.Goldenbaum
* Created: 10.01.23 10:48
* Updated: -
*
* Description:
*  -
*/

namespace Tests\issues;

use Tests\live\LiveMailboxTestCase;
use Profitbyte\PHPIMAP\Exceptions\AuthFailedException;
use Profitbyte\PHPIMAP\Exceptions\ConnectionFailedException;
use Profitbyte\PHPIMAP\Exceptions\EventNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\FolderFetchingException;
use Profitbyte\PHPIMAP\Exceptions\ImapBadRequestException;
use Profitbyte\PHPIMAP\Exceptions\ImapServerErrorException;
use Profitbyte\PHPIMAP\Exceptions\InvalidMessageDateException;
use Profitbyte\PHPIMAP\Exceptions\MaskNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\MessageContentFetchingException;
use Profitbyte\PHPIMAP\Exceptions\MessageFlagException;
use Profitbyte\PHPIMAP\Exceptions\MessageHeaderFetchingException;
use Profitbyte\PHPIMAP\Exceptions\MessageNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\ResponseException;
use Profitbyte\PHPIMAP\Exceptions\RuntimeException;

class Issue379Test extends LiveMailboxTestCase {

    /**
     * Test issue #379 - Message::getSize() added
     * @return void
     * @throws AuthFailedException
     * @throws ConnectionFailedException
     * @throws EventNotFoundException
     * @throws FolderFetchingException
     * @throws ImapBadRequestException
     * @throws ImapServerErrorException
     * @throws InvalidMessageDateException
     * @throws MessageContentFetchingException
     * @throws MessageFlagException
     * @throws MessageHeaderFetchingException
     * @throws MessageNotFoundException
     * @throws ResponseException
     * @throws RuntimeException
     * @throws MaskNotFoundException
     */
    public function testIssue(): void {
        $folder = $this->getFolder('INBOX');

        $message = $this->appendMessageTemplate($folder, "plain.eml");
        $this->assertEquals(214, $message->getSize());

        // Clean up
        $this->assertTrue($message->delete(true));
    }

}