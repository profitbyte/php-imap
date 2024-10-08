<?php
/*
* File:     MessageCollection.php
* Category: Collection
* Author:   M. Goldenbaum
* Created:  16.03.18 03:13
* Updated:  -
*
* Description:
*  -
*/

namespace Profitbyte\PHPIMAP\Support;

use Illuminate\Support\Collection;
use Profitbyte\PHPIMAP\Message;

/**
 * Class MessageCollection
 *
 * @package Profitbyte\PHPIMAP\Support
 * @implements Collection<int, Message>
 */
class MessageCollection extends PaginatedCollection {

}
