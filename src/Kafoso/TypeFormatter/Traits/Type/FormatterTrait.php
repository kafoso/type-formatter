<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Traits\Type;

use Kafoso\TypeFormatter\TypeFormatter;
use Kafoso\TypeFormatter\Type\FormatterInterface;

trait FormatterTrait
{
    /**
     * @var TypeFormatter
     */
    protected $typeFormatter;

    /**
     * @var bool
     */
    protected $isPrependingType = false;

    /**
     * @var bool
     */
    protected $isSamplifying = false;

    public function withIsPrependingType(bool $isPrependingType): FormatterInterface
    {
        $clone = clone $this;
        $clone->isPrependingType = $isPrependingType;
        return $clone;
    }

    public function withIsSamplifying(bool $isSamplifying): FormatterInterface
    {
        $clone = clone $this;
        $clone->isSamplifying = $isSamplifying;
        return $clone;
    }

    public function setTypeFormatter(TypeFormatter $typeFormatter): void
    {
        $this->typeFormatter = $typeFormatter;
    }

    public function getTypeFormatter(): TypeFormatter
    {
        return $this->typeFormatter;
    }

    public function isPrependingType(): bool
    {
        return $this->isPrependingType;
    }

    public function isSamplifying(): bool
    {
        return $this->isSamplifying;
    }
}
