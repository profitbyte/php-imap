<?php
/*
* File: MessageTest.php
* Category: -
* Author: M.Goldenbaum
* Created: 28.12.22 18:11
* Updated: -
*
* Description:
*  -
*/

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Profitbyte\PHPIMAP\Attachment;
use Profitbyte\PHPIMAP\Attribute;
use Profitbyte\PHPIMAP\Client;
use Profitbyte\PHPIMAP\Config;
use Profitbyte\PHPIMAP\Connection\Protocols\Response;
use Profitbyte\PHPIMAP\Exceptions\EventNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\InvalidMessageDateException;
use Profitbyte\PHPIMAP\Exceptions\MessageContentFetchingException;
use Profitbyte\PHPIMAP\Exceptions\MessageFlagException;
use Profitbyte\PHPIMAP\Exceptions\MessageNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\MessageSizeFetchingException;
use Profitbyte\PHPIMAP\Exceptions\ResponseException;
use Profitbyte\PHPIMAP\IMAP;
use Profitbyte\PHPIMAP\Message;
use Profitbyte\PHPIMAP\Connection\Protocols\ImapProtocol;
use Profitbyte\PHPIMAP\Exceptions\AuthFailedException;
use Profitbyte\PHPIMAP\Exceptions\ConnectionFailedException;
use Profitbyte\PHPIMAP\Exceptions\ImapBadRequestException;
use Profitbyte\PHPIMAP\Exceptions\ImapServerErrorException;
use Profitbyte\PHPIMAP\Exceptions\MaskNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\RuntimeException;

class MessageTest extends TestCase {

    /** @var Message $message */
    protected Message $message;

    /** @var Client $client */
    protected Client $client;

    /** @var MockObject ImapProtocol mockup */
    protected MockObject $protocol;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void {
        $config = Config::make([
                                   "accounts" => [
                                       "default" => [
                                           'protocol'   => 'imap',
                                           'encryption' => 'ssl',
                                           'username'   => 'foo@domain.tld',
                                           'password'   => 'bar',
                                           'proxy'      => [
                                               'socket'          => null,
                                               'request_fulluri' => false,
                                               'username'        => null,
                                               'password'        => null,
                                           ],
                                       ]]
                               ]);
        $this->client = new Client($config);
    }

    /**
     * Message test
     *
     * @return void
     * @throws AuthFailedException
     * @throws ConnectionFailedException
     * @throws EventNotFoundException
     * @throws ImapBadRequestException
     * @throws ImapServerErrorException
     * @throws InvalidMessageDateException
     * @throws MessageContentFetchingException
     * @throws MessageFlagException
     * @throws MessageNotFoundException
     * @throws MessageSizeFetchingException
     * @throws ReflectionException
     * @throws ResponseException
     * @throws RuntimeException
     */
    public function testMessage(): void {
        $this->createNewProtocolMockup();

        $email = file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, "messages", "1366671050@github.com.eml"]));
        if(!str_contains($email, "\r\n")){
            $email = str_replace("\n", "\r\n", $email);
        }

        $raw_header = substr($email, 0, strpos($email, "\r\n\r\n"));
        $raw_body = substr($email, strlen($raw_header)+8);

        $this->protocol->expects($this->any())->method('getUid')->willReturn(Response::empty()->setResult(22));
        $this->protocol->expects($this->any())->method('getMessageNumber')->willReturn(Response::empty()->setResult(21));
        $this->protocol->expects($this->any())->method('flags')->willReturn(Response::empty()->setResult([22 => [0 => "\\Seen"]]));

        self::assertNotEmpty($this->client->openFolder("INBOX"));

        $message = Message::make(22, null, $this->client, $raw_header, $raw_body, [0 => "\\Seen"], IMAP::ST_UID);

        self::assertInstanceOf(Client::class, $message->getClient());
        self::assertSame(22, $message->uid);
        self::assertSame(21, $message->msgn);
        self::assertContains("Seen", $message->flags()->toArray());

        $subject = $message->get("subject");
        $returnPath = $message->get("Return-Path");

        self::assertInstanceOf(Attribute::class, $subject);
        self::assertSame("Re: [Profitbyte/php-imap] Read all folders? (Issue #349)", $subject->toString());
        self::assertSame("Re: [Profitbyte/php-imap] Read all folders? (Issue #349)", (string)$message->subject);
        self::assertSame("<noreply@github.com>", $returnPath->toString());
        self::assertSame("return_path", $returnPath->getName());
        self::assertSame("-4.299", (string)$message->get("X-Spam-Score"));
        self::assertSame("Profitbyte/php-imap/issues/349/1365266070@github.com", (string)$message->get("Message-ID"));
        self::assertSame(6, $message->get("received")->count());
        self::assertSame(IMAP::MESSAGE_PRIORITY_UNKNOWN, (int)$message->get("priority")());
    }

    /**
     * Test getMessageNumber
     *
     * @return void
     * @throws AuthFailedException
     * @throws ConnectionFailedException
     * @throws ImapBadRequestException
     * @throws ImapServerErrorException
     * @throws MessageNotFoundException
     * @throws ResponseException
     * @throws RuntimeException
     */
    public function testGetMessageNumber(): void {
        $this->createNewProtocolMockup();
        $this->protocol->expects($this->any())->method('getMessageNumber')->willReturn(Response::empty()->setResult(""));

        self::assertNotEmpty($this->client->openFolder("INBOX"));

        try {
            $this->client->getConnection()->getMessageNumber(21)->validatedData();
            $this->fail("Message number should not exist");
        } catch (ResponseException $e) {
            self::assertTrue(true);
        }

    }

    /**
     * Test loadMessageFromFile
     *
     * @return void
     * @throws AuthFailedException
     * @throws ConnectionFailedException
     * @throws ImapBadRequestException
     * @throws ImapServerErrorException
     * @throws InvalidMessageDateException
     * @throws MaskNotFoundException
     * @throws MessageContentFetchingException
     * @throws MessageNotFoundException
     * @throws ReflectionException
     * @throws ResponseException
     * @throws RuntimeException
     * @throws MessageSizeFetchingException
     */
    public function testLoadMessageFromFile(): void {
        $filename = implode(DIRECTORY_SEPARATOR, [__DIR__, "messages", "1366671050@github.com.eml"]);
        $message = Message::fromFile($filename);

        $subject = $message->get("subject");
        $returnPath = $message->get("Return-Path");

        self::assertInstanceOf(Attribute::class, $subject);
        self::assertSame("Re: [Profitbyte/php-imap] Read all folders? (Issue #349)", $subject->toString());
        self::assertSame("Re: [Profitbyte/php-imap] Read all folders? (Issue #349)", (string)$message->subject);
        self::assertSame("<noreply@github.com>", $returnPath->toString());
        self::assertSame("return_path", $returnPath->getName());
        self::assertSame("-4.299", (string)$message->get("X-Spam-Score"));
        self::assertSame("Profitbyte/php-imap/issues/349/1365266070@github.com", (string)$message->get("Message-ID"));
        self::assertSame(6, $message->get("received")->count());
        self::assertSame(IMAP::MESSAGE_PRIORITY_UNKNOWN, (int)$message->get("priority")());

        self::assertNull($message->getClient());
        self::assertSame(0, $message->uid);

        $filename = implode(DIRECTORY_SEPARATOR, [__DIR__, "messages", "example_attachment.eml"]);
        $message = Message::fromFile($filename);

        $subject = $message->get("subject");
        $returnPath = $message->get("Return-Path");

        self::assertInstanceOf(Attribute::class, $subject);
        self::assertSame("ogqMVHhz7swLaq2PfSWsZj0k99w8wtMbrb4RuHdNg53i76B7icIIM0zIWpwGFtnk", $subject->toString());
        self::assertSame("ogqMVHhz7swLaq2PfSWsZj0k99w8wtMbrb4RuHdNg53i76B7icIIM0zIWpwGFtnk", (string)$message->subject);
        self::assertSame("<someone@domain.tld>", $returnPath->toString());
        self::assertSame("return_path", $returnPath->getName());
        self::assertSame("1.103", (string)$message->get("X-Spam-Score"));
        self::assertSame("d3a5e91963cb805cee975687d5acb1c6@swift.generated", (string)$message->get("Message-ID"));
        self::assertSame(5, $message->get("received")->count());
        self::assertSame(IMAP::MESSAGE_PRIORITY_HIGHEST, (int)$message->get("priority")());

        self::assertNull($message->getClient());
        self::assertSame(0, $message->uid);
        self::assertSame(1, $message->getAttachments()->count());

        /** @var Attachment $attachment */
        $attachment = $message->getAttachments()->first();
        self::assertSame("attachment", $attachment->disposition);
        self::assertSame("znk551MP3TP3WPp9Kl1gnLErrWEgkJFAtvaKqkTgrk3dKI8dX38YT8BaVxRcOERN", $attachment->content);
        self::assertSame("application/octet-stream", $attachment->content_type);
        self::assertSame("6mfFxiU5Yhv9WYJx.txt", $attachment->name);
        self::assertSame(2, $attachment->part_number);
        self::assertSame("text", $attachment->type);
        self::assertNotEmpty($attachment->id);
        self::assertSame(90, $attachment->size);
        self::assertSame("txt", $attachment->getExtension());
        self::assertInstanceOf(Message::class, $attachment->getMessage());
        self::assertSame("text/plain", $attachment->getMimeType());
    }

    /**
     * Test issue #348
     *
     * @return void
     * @throws AuthFailedException
     * @throws ConnectionFailedException
     * @throws ImapBadRequestException
     * @throws ImapServerErrorException
     * @throws InvalidMessageDateException
     * @throws MaskNotFoundException
     * @throws MessageContentFetchingException
     * @throws ReflectionException
     * @throws ResponseException
     * @throws RuntimeException
     */
    public function testIssue348() {
        $filename = implode(DIRECTORY_SEPARATOR, [__DIR__, "messages", "issue-348.eml"]);
        $message = Message::fromFile($filename);

        self::assertSame(1, $message->getAttachments()->count());

        /** @var Attachment $attachment */
        $attachment = $message->getAttachments()->first();

        self::assertSame("attachment", $attachment->disposition);
        self::assertSame("application/pdf", $attachment->content_type);
        self::assertSame("Kelvinsong—Font_test_page_bold.pdf", $attachment->name);
        self::assertSame(1, $attachment->part_number);
        self::assertSame("text", $attachment->type);
        self::assertNotEmpty($attachment->id);
        self::assertSame(92384, $attachment->size);
        self::assertSame("pdf", $attachment->getExtension());
        self::assertInstanceOf(Message::class, $attachment->getMessage());
        self::assertSame("application/pdf", $attachment->getMimeType());
    }

    /**
     * Create a new protocol mockup
     *
     * @return void
     */
    protected function createNewProtocolMockup(): void {
        $this->protocol = $this->createMock(ImapProtocol::class);

        $this->protocol->expects($this->any())->method('createStream')->willReturn(true);
        $this->protocol->expects($this->any())->method('connected')->willReturn(true);
        $this->protocol->expects($this->any())->method('getConnectionTimeout')->willReturn(30);
        $this->protocol->expects($this->any())->method('logout')->willReturn(Response::empty()->setResponse([
                                                                                 0 => "BYE Logging out\r\n",
                                                                                 1 => "OK Logout completed (0.001 + 0.000 secs).\r\n",
                                                                             ]));
        $this->protocol->expects($this->any())->method('selectFolder')->willReturn(Response::empty()->setResponse([
                                                                                       "flags"       => [
                                                                                           0 => [
                                                                                               0 => "\Answered",
                                                                                               1 => "\Flagged",
                                                                                               2 => "\Deleted",
                                                                                               3 => "\Seen",
                                                                                               4 => "\Draft",
                                                                                               5 => "NonJunk",
                                                                                               6 => "unknown-1",
                                                                                           ],
                                                                                       ],
                                                                                       "exists"      => 139,
                                                                                       "recent"      => 0,
                                                                                       "unseen"      => 94,
                                                                                       "uidvalidity" => 1488899637,
                                                                                       "uidnext"     => 278,
                                                                                   ]));

        $this->client->connection = $this->protocol;
    }
}