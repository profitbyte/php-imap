<?php
/*
* File: Issue407Test.php
* Category: Test
* Author: M.Goldenbaum
* Created: 23.06.23 21:40
* Updated: -
*
* Description:
*  -
*/

namespace Tests\issues;

use PHPUnit\Framework\TestCase;
use Tests\live\LiveMailboxTestCase;
use Profitbyte\PHPIMAP\Folder;
use Profitbyte\PHPIMAP\IMAP;
use Profitbyte\PHPIMAP\Message;

class Issue407Test extends LiveMailboxTestCase {

    /**
     * @return void
     * @throws \Profitbyte\PHPIMAP\Exceptions\AuthFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\EventNotFoundException
     * @throws \Profitbyte\PHPIMAP\Exceptions\FolderFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ImapBadRequestException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ImapServerErrorException
     * @throws \Profitbyte\PHPIMAP\Exceptions\InvalidMessageDateException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MaskNotFoundException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageContentFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageFlagException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageHeaderFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ResponseException
     * @throws \Profitbyte\PHPIMAP\Exceptions\RuntimeException
     */
    public function testIssue() {
        $folder = $this->getFolder('INBOX');
        self::assertInstanceOf(Folder::class, $folder);

        $message = $this->appendMessageTemplate($folder, "plain.eml");
        self::assertInstanceOf(Message::class, $message);

        $message->setFlag("Seen");

        $flags = $this->getClient()->getConnection()->flags($message->uid, IMAP::ST_UID)->validatedData();

        self::assertIsArray($flags);
        self::assertSame(1, count($flags));
        self::assertSame("\\Seen", $flags[$message->uid][0]);

        $message->delete();
    }

}