<?php
/*
* File: Issue413Test.php
* Category: Test
* Author: M.Goldenbaum
* Created: 23.06.23 21:09
* Updated: -
*
* Description:
*  -
*/

namespace Tests\issues;

use PHPUnit\Framework\TestCase;
use Tests\live\LiveMailboxTestCase;
use Profitbyte\PHPIMAP\Config;
use Profitbyte\PHPIMAP\Folder;
use Profitbyte\PHPIMAP\Message;

class Issue413Test extends LiveMailboxTestCase {

    /**
     * Live server test
     *
     * @return void
     * @throws \Profitbyte\PHPIMAP\Exceptions\AuthFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\EventNotFoundException
     * @throws \Profitbyte\PHPIMAP\Exceptions\FolderFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\GetMessagesFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ImapBadRequestException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ImapServerErrorException
     * @throws \Profitbyte\PHPIMAP\Exceptions\InvalidMessageDateException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MaskNotFoundException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageContentFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageFlagException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageHeaderFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageNotFoundException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ResponseException
     * @throws \Profitbyte\PHPIMAP\Exceptions\RuntimeException
     */
    public function testLiveIssueEmail() {
        $folder = $this->getFolder('INBOX');
        self::assertInstanceOf(Folder::class, $folder);

        /** @var Message $message */
        $_message = $this->appendMessageTemplate($folder, 'issue-413.eml');

        $message = $folder->messages()->getMessageByMsgn($_message->msgn);
        self::assertEquals($message->uid, $_message->uid);

        self::assertSame("Test Message", (string)$message->subject);
        self::assertSame("This is just a test, so ignore it (if you can!)\r\n\r\nTony Marston", $message->getTextBody());

        $message->delete();
    }

    /**
     * Static parsing test
     *
     * @return void
     * @throws \ReflectionException
     * @throws \Profitbyte\PHPIMAP\Exceptions\AuthFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ConnectionFailedException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ImapBadRequestException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ImapServerErrorException
     * @throws \Profitbyte\PHPIMAP\Exceptions\InvalidMessageDateException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MaskNotFoundException
     * @throws \Profitbyte\PHPIMAP\Exceptions\MessageContentFetchingException
     * @throws \Profitbyte\PHPIMAP\Exceptions\ResponseException
     * @throws \Profitbyte\PHPIMAP\Exceptions\RuntimeException
     */
    public function testIssueEmail() {
        $filename = implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "messages", "issue-413.eml"]);
        $message = Message::fromFile($filename);

        self::assertSame("Test Message", (string)$message->subject);
        self::assertSame("This is just a test, so ignore it (if you can!)\r\n\r\nTony Marston", $message->getTextBody());
    }

}