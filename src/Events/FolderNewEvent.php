<?php
/*
* File:     FolderNewEvent.php
* Category: Event
* Author:   M. Goldenbaum
* Created:  25.11.20 22:21
* Updated:  -
*
* Description:
*  -
*/

namespace Profitbyte\PHPIMAP\Events;

use Profitbyte\PHPIMAP\Folder;

/**
 * Class FolderNewEvent
 *
 * @package Profitbyte\PHPIMAP\Events
 */
class FolderNewEvent extends Event {

    /** @var Folder $folder */
    public Folder $folder;

    /**
     * Create a new event instance.
     * @var Folder[] $folders
     *
     * @return void
     */
    public function __construct(array $folders) {
        $this->folder = $folders[0];
    }
}
