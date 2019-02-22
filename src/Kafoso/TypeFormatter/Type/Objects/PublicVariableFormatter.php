<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * Prints variables with public access.
 */
class PublicVariableFormatter extends AbstractFormatter implements ObjectFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format($object): ?string
    {
        if (false == is_object($object)) {
            return null; // Pass on
        }
        if (false == $this->isQualified($object)) {
            return null;
        }

        $depthMaximum = $this->getTypeFormatter()->getObjectDepthMaximum();
        $depthCurrent = min($depthMaximum, $this->getTypeFormatter()->getObjectDepthCurrent());
        $return = sprintf("\\%s {", DefaultObjectFormatter::getClassName($object));
        if ($depthCurrent >= $depthMaximum) {
            $return .= TypeFormatter::SAMPLE_ELLIPSIS . "}";
            return $return;
        }
        $vars = get_object_vars($object);
        if ($vars) {
            $segments = [];
            $subTypeFormatter = $this->getTypeFormatter();
            $subTypeFormatter = $subTypeFormatter->withArrayDepthCurrent(
                $this->getTypeFormatter()->getArrayDepthCurrent()+1
            );
            $subTypeFormatter = $subTypeFormatter->withObjectDepthCurrent($depthCurrent+1);
            foreach ($vars as $key => $var) {
                if ($this->isPrependingType()) {
                    $segments[] = sprintf(
                        "%s = %s",
                        $key,
                        $subTypeFormatter->typeCast($var, $this->isSamplifying())
                    );
                } else {
                    $segments[] = sprintf(
                        "%s = %s",
                        $key,
                        $subTypeFormatter->cast($var, $this->isSamplifying())
                    );
                }
            }
            $return .= implode(", ", $segments);
        }
        $return .= "}";

        if ($this->isPrependingType()) {
            $return = sprintf(
                "(object) %s",
                $return
            );
        }
        return $return;
    }

    public function isQualified($object): bool
    {
        if (false == is_object($object)) {
            return false;
        }
        return boolval(get_object_vars($object));
    }
}
