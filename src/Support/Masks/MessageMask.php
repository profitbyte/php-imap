<?php
/*
* File: MessageMask.php
* Category: Mask
* Author: M.Goldenbaum
* Created: 14.03.19 20:49
* Updated: -
*
* Description:
*  -
*/

namespace Profitbyte\PHPIMAP\Support\Masks;

use Profitbyte\PHPIMAP\Attachment;
use Profitbyte\PHPIMAP\Message;

/**
 * Class MessageMask
 *
 * @package Profitbyte\PHPIMAP\Support\Masks
 * @mixin Message
 */
class MessageMask extends Mask {

    /** @var Message $parent */
    protected mixed $parent;

    /**
     * Get the message html body
     *
     * @return null
     */
    public function getHtmlBody(){
        $bodies = $this->parent->getBodies();
        if (!isset($bodies['html'])) {
            return null;
        }

        if(is_object($bodies['html']) && property_exists($bodies['html'], 'content')) {
            return $bodies['html']->content;
        }
        return $bodies['html'];
    }

    /**
     * Get the Message html body filtered by an optional callback
     * @param callable|null $callback
     *
     * @return string|null
     */
    public function getCustomHTMLBody(?callable $callback = null): ?string {
        $body = $this->getHtmlBody();
        if($body === null) return null;

        if ($callback !== null) {
            $aAttachment = $this->parent->getAttachments();
            $aAttachment->each(function($oAttachment) use(&$body, $callback) {
                /** @var Attachment $oAttachment */
                if(is_callable($callback)) {
                    $body = $callback($body, $oAttachment);
                }elseif(is_string($callback)) {
                    call_user_func($callback, [$body, $oAttachment]);
                }
            });
        }

        return $body;
    }

    /**
     * Get the Message html body with embedded base64 images
     * the resulting $body.
     *
     * @return string|null
     */
    public function getHTMLBodyWithEmbeddedBase64Images(): ?string {
        return $this->getCustomHTMLBody(function($body, $oAttachment){
            /** @var Attachment $oAttachment */
            if ($oAttachment->id) {
                $body = str_replace('cid:'.$oAttachment->id, 'data:'.$oAttachment->getContentType().';base64, '.base64_encode($oAttachment->getContent()), $body);
            }

            return $body;
        });
    }
}