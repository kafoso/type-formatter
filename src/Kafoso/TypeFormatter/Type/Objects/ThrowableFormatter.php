<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type\Objects;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;
use Kafoso\TypeFormatter\Type\ObjectFormatterInterface;
use Kafoso\TypeFormatter\TypeFormatter;

/**
 * Formats instances of \Throwable.
 */
class ThrowableFormatter extends AbstractFormatter implements ObjectFormatterInterface
{
    /**
     * @var int
     */
    protected $messageMaximumLength = 0;

    public function __construct(int $messageMaximumLength = 1000)
    {
        $this->messageMaximumLength = max(0, $messageMaximumLength);
    }

    /**
     * @inheritDoc
     */
    public function format($object): ?string
    {
        if (false == $this->isQualified($object)) {
            return null;
        }
        return $this->_renderThrowable($object, $this->getTypeFormatter());
    }

    /**
     * @var object $object
     */
    public function isQualified($object): bool
    {
        if (false == is_object($object)) {
            return false;
        }
        return ($object instanceof \Throwable);
    }

    /**
     * Recursive.
     */
    protected function _renderThrowable(\Throwable $throwable, TypeFormatter $typeFormatter): string
    {
        $typeFormatterMessage = $typeFormatter->withStringSampleSize($this->messageMaximumLength);
        $return = sprintf(
            "\\%s {\$code = %s, \$file = %s, \$line = %s, \$message = %s",
            get_class($throwable),
            $typeFormatter->cast($throwable->getCode()),
            $typeFormatter->cast($throwable->getFile(), false),
            $typeFormatter->cast($throwable->getLine()),
            $typeFormatter->cast($throwable->getMessage())
        );
        if ($throwable->getPrevious()) {
            if ($typeFormatter->getObjectDepthCurrent() < $typeFormatter->getObjectDepthMaximum()) {
                $subTypeFormatter = $typeFormatter->withObjectDepthCurrent($typeFormatter->getObjectDepthCurrent()+1);
                $return .= sprintf(
                    ", \$previous = %s}",
                    $this->_renderThrowable($throwable->getPrevious(), $subTypeFormatter)
                );
            } else {
                $return .= sprintf(
                    ", \$previous = \\%s {...}}",
                    get_class($throwable->getPrevious())
                );
            }
        } else {
            $return .= ", \$previous = null}";
        }
        return $return;
    }
}
