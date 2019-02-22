<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;

class DefaultResourceFormatter extends AbstractFormatter implements ResourceFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format($resource): ?string
    {
        if (false == is_resource($resource)) {
            throw new \InvalidArgumentException(sprintf(
                "Expects argument \$resource to be a resource. Found: %s",
                TypeFormatter::create()->typeCast($resource)
            ));
        }
        $return = sprintf(
            "`%s` {$resource}",
            get_resource_type($resource)
        );
        if ($this->isPrependingType()) {
            $return = "(resource) {$return}";
        }
        return $return;
    }

    public function formatEnsureString($resource): string
    {
        return strval($this->format($resource));
    }
}
