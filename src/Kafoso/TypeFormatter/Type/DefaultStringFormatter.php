<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

use Kafoso\TypeFormatter\TypeFormatter;
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;

class DefaultStringFormatter extends AbstractFormatter implements StringFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format(string $string): ?string
    {
        $length = mb_strlen($string, (string)$this->getTypeFormatter()->getEncoding());
        $maskedLength = $length;
        $return = "";
        $isSample = false;
        $isMasked = false;
        $encodingStr = (string)$this->getTypeFormatter()->getEncoding();
        if ($this->isSamplifying()) {
            if ("" === $string) {
                $return = $this->getTypeFormatter()->quoteAndEscape("");
            } elseif ($this->getTypeFormatter()->getStringSampleSize() > 0) {
                $masked = $this->getTypeFormatter()->maskString($string);
                if ($length > $this->getTypeFormatter()->getStringSampleSize()) {
                    $ellipsisLength = mb_strlen(TypeFormatter::SAMPLE_ELLIPSIS, $encodingStr);
                    $return = mb_substr(
                        $masked,
                        0,
                        max(0, ($this->getTypeFormatter()->getStringSampleSize() - ($ellipsisLength+1))),
                        $encodingStr
                    );
                    if ($return) {
                        $return .= " " . TypeFormatter::SAMPLE_ELLIPSIS;
                    } else {
                        $return = TypeFormatter::SAMPLE_ELLIPSIS;
                    }
                    $isSample = true;
                } else {
                    $return = $masked;
                }
                if ($masked !== $string) {
                    $isMasked = true;
                    $maskedLength = mb_strlen($masked, $encodingStr);
                }
                $return = $this->getTypeFormatter()->quoteAndEscape($return);
            } else {
                $return = $this->getTypeFormatter()->quoteAndEscape(TypeFormatter::SAMPLE_ELLIPSIS);
                $isSample = true;
            }
        } else {
            $masked = $this->getTypeFormatter()->maskString($string);
            if ($masked !== $string) {
                $isMasked = true;
                $maskedLength = mb_strlen($masked, $encodingStr);
            }
            $return = $this->getTypeFormatter()->quoteAndEscape($masked);
        }
        if ($this->isPrependingType()) {
            $return = sprintf(
                "(string(%d)) %s",
                $maskedLength,
                $return
            );
        }
        $modifiers = [];
        if ($isSample) {
            $modifiers[] = "sample";
        }
        if ($isMasked) {
            $modifiers[] = "masked";
        }
        if ($modifiers) {
            $return .= sprintf(" (%s)", implode(",", $modifiers));
        }
        return $return;
    }

    public function formatEnsureString(string $string): string
    {
        return strval($this->format($string));
    }
}
