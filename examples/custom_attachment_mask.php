<?php
/*
* File: custom_message_mask.php
* Category: Example
* Author: M.Goldenbaum
* Created: 14.03.19 18:47
* Updated: -
*
* Description:
*  -
*/

class CustomAttachmentMask extends \Profitbyte\PHPIMAP\Support\Masks\AttachmentMask {

    /**
     * New custom method which can be called through a mask
     * @return string
     */
    public function token(): string {
        return implode('-', [$this->id, $this->getMessage()->getUid(), $this->name]);
    }

    /**
     * Custom attachment saving method
     * @return bool
     */
    public function custom_save(): bool {
        $path = "foo".DIRECTORY_SEPARATOR."bar".DIRECTORY_SEPARATOR;
        $filename = $this->token();

        return file_put_contents($path.$filename, $this->getContent()) !== false;
    }

}

$cm = new \Profitbyte\PHPIMAP\ClientManager('path/to/config/imap.php');

/** @var \Profitbyte\PHPIMAP\Client $client */
$client = $cm->account('default');
$client->connect();
$client->setDefaultAttachmentMask(CustomAttachmentMask::class);

/** @var \Profitbyte\PHPIMAP\Folder $folder */
$folder = $client->getFolder('INBOX');

/** @var \Profitbyte\PHPIMAP\Message $message */
$message = $folder->query()->limit(1)->get()->first();

/** @var \Profitbyte\PHPIMAP\Attachment $attachment */
$attachment = $message->getAttachments()->first();

/** @var CustomAttachmentMask $masked_attachment */
$masked_attachment = $attachment->mask();

echo 'Token for uid ['.$masked_attachment->getMessage()->getUid().']: '.$masked_attachment->token();

$masked_attachment->custom_save();