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
use Profitbyte\PHPIMAP\Exceptions\MaskNotFoundException;
use Profitbyte\PHPIMAP\Exceptions\ResponseException;
use Profitbyte\PHPIMAP\Exceptions\RuntimeException;
use Profitbyte\PHPIMAP\Folder;

class Issue383Test extends LiveMailboxTestCase {

    /**
     * Test issue #383 - Does not work when a folder name contains umlauts: Entwürfe
     * @return void
     * @throws AuthFailedException
     * @throws ConnectionFailedException
     * @throws EventNotFoundException
     * @throws FolderFetchingException
     * @throws ImapBadRequestException
     * @throws ImapServerErrorException
     * @throws ResponseException
     * @throws RuntimeException
     * @throws MaskNotFoundException
     */
    public function testIssue(): void {
        $client = $this->getClient();
        $client->connect();

        $delimiter = $this->getManager()->getConfig()->get("options.delimiter");
        $folder_path = implode($delimiter, ['INBOX', 'Entwürfe+']);

        $folder = $client->getFolder($folder_path);
        $this->deleteFolder($folder);

        $folder = $client->createFolder($folder_path, false);
        self::assertInstanceOf(Folder::class, $folder);

        $folder = $this->getFolder($folder_path);
        self::assertInstanceOf(Folder::class, $folder);

        $this->assertEquals('Entwürfe+', $folder->name);
        $this->assertEquals($folder_path, $folder->full_name);

        $folder_path = implode($delimiter, ['INBOX', 'Entw&APw-rfe+']);
        $this->assertEquals($folder_path, $folder->path);

        // Clean up
        if ($this->deleteFolder($folder) === false) {
            $this->fail("Could not delete folder: " . $folder->path);
        }
    }
}