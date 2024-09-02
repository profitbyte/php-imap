<?php
/*
* File: Issue393Test.php
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

class Issue393Test extends LiveMailboxTestCase {

    /**
     * Test issue #393 - "Empty response" when calling getFolders()
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
        $pattern = implode($delimiter, ['doesnt_exist', '%']);

        $folder = $client->getFolder('doesnt_exist');
        $this->deleteFolder($folder);

        $folders = $client->getFolders(true, $pattern, true);
        self::assertCount(0, $folders);

        try {
            $client->getFolders(true, $pattern, false);
            $this->fail('Expected FolderFetchingException::class exception not thrown');
        } catch (FolderFetchingException $e) {
            self::assertInstanceOf(FolderFetchingException::class, $e);
        }
    }
}