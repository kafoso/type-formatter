<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter;

use Kafoso\TypeFormatter\Type\DefaultArrayFormatter;
use Kafoso\TypeFormatter\Type\DefaultObjectFormatter;
use Kafoso\TypeFormatter\Type\DefaultResourceFormatter;
use Kafoso\TypeFormatter\Type\DefaultStringFormatter;
use Kafoso\TypeFormatter\Collection\EncryptedStringCollection;
use Kafoso\TypeFormatter\Collection\Type\ArrayFormatterCollection;
use Kafoso\TypeFormatter\Collection\Type\ObjectFormatterCollection;
use Kafoso\TypeFormatter\Collection\Type\ResourceFormatterCollection;
use Kafoso\TypeFormatter\Collection\Type\StringFormatterCollection;

/**
 * Immutable. Use `with*` methods to generate copies.
 */
class TypeFormatter
{
    const ARRAY_DEPTH_CURRENT_DEFAULT = 0;
    const ARRAY_DEPTH_MAXIMUM_DEFAULT = 0;
    const ARRAY_SAMPLE_SIZE_DEFAULT = 3;
    const OBJECT_DEPTH_CURRENT_DEFAULT = 0;
    const OBJECT_DEPTH_MAXIMUM_DEFAULT = 1;
    const SAMPLE_ELLIPSIS = "...";
    const STRING_MASK = "*****";
    const STRING_QUOTING_CHARACTER_DEFAULT = '"';
    const STRING_SAMPLE_SIZE_DEFAULT = 200;

    /**
     * @var null|TypeFormatter
     */
    protected static $default = null;

    /**
     * @var array (TypeFormatter[])
     */
    protected static $variations = [];

    /**
     * @var DefaultStringFormatter
     */
    protected $defaultStringFormatter;

    /**
     * @var DefaultArrayFormatter
     */
    protected $defaultArrayFormatter;

    /**
     * @var DefaultArrayFormatter
     */
    protected $defaultObjectFormatter;

    /**
     * @var DefaultResourceFormatter
     */
    protected $defaultResourceFormatter;

    /**
     * @var Encoding
     */
    protected $encoding = null;

    /**
     * @var int
     */
    protected $arraySampleSize = 0;

    /**
     * @var int
     */
    protected $arrayDepthCurrent = 0;

    /**
     * @var int
     */
    protected $arrayDepthMaximum = 0;

    /**
     * @var int
     */
    protected $stringSampleSize = 0;

    /**
     * @var string
     */
    protected $stringQuotingCharacter = 0;

    /**
     * @var null|EncryptedStringCollection
     */
    protected $maskedEncryptedStringCollection = null;

    /**
     * @var null|StringFormatterCollection
     */
    protected $customStringFormatterCollection = null;

    /**
     * @var null|ArrayFormatterCollection
     */
    protected $customArrayFormatterCollection = null;

    /**
     * @var null|ObjectFormatterCollection
     */
    protected $customObjectFormatterCollection = null;

    /**
     * @var null|ResourceFormatterCollection
     */
    protected $customResourceFormatterCollection = null;

    /**
     * @throws \RuntimeException
     */
    public function __construct(Encoding $encoding)
    {
        try {
            if (null !== $this->encoding) {
                throw new \LogicException(sprintf(
                    "\\%s is immutable. You are not supposed to call `__construct` directly",
                    get_class($this)
                ));
            }
            $this->encoding = $encoding;
            $this->arraySampleSize = static::ARRAY_SAMPLE_SIZE_DEFAULT;
            $this->arrayDepthCurrent = static::ARRAY_DEPTH_CURRENT_DEFAULT;
            $this->arrayDepthMaximum = static::ARRAY_DEPTH_MAXIMUM_DEFAULT;
            $this->objectDepthCurrent = static::OBJECT_DEPTH_CURRENT_DEFAULT;
            $this->objectDepthMaximum = static::OBJECT_DEPTH_MAXIMUM_DEFAULT;
            $this->stringSampleSize = static::STRING_SAMPLE_SIZE_DEFAULT;
            $this->stringQuotingCharacter = static::STRING_QUOTING_CHARACTER_DEFAULT;
            $this->defaultStringFormatter = new DefaultStringFormatter;
            $this->defaultStringFormatter->setTypeFormatter($this);
            $this->defaultArrayFormatter = new DefaultArrayFormatter;
            $this->defaultArrayFormatter->setTypeFormatter($this);
            $this->defaultObjectFormatter = new DefaultObjectFormatter;
            $this->defaultObjectFormatter->setTypeFormatter($this);
            $this->defaultResourceFormatter = new DefaultResourceFormatter;
            $this->defaultResourceFormatter->setTypeFormatter($this);
            $this->maskedEncryptedStringCollection = new EncryptedStringCollection;
        } catch (\Throwable $t) {
            throw new \RuntimeException(sprintf(
                "Failed to construct \\%s",
                get_class($this)
            ), 0, $t);
        }
    }

    public function __clone()
    {
        $this->defaultArrayFormatter = clone $this->defaultArrayFormatter;
        $this->defaultArrayFormatter->setTypeFormatter($this);
        $this->defaultObjectFormatter = clone $this->defaultObjectFormatter;
        $this->defaultObjectFormatter->setTypeFormatter($this);
        $this->defaultResourceFormatter = clone $this->defaultResourceFormatter;
        $this->defaultResourceFormatter->setTypeFormatter($this);
        $this->defaultStringFormatter = clone $this->defaultStringFormatter;
        $this->defaultStringFormatter->setTypeFormatter($this);
        $this->maskedEncryptedStringCollection = (
            $this->maskedEncryptedStringCollection
            ? clone $this->maskedEncryptedStringCollection
            : null
        );
        $this->customArrayFormatterCollection = (
            $this->customArrayFormatterCollection
            ? clone $this->customArrayFormatterCollection
            : null
        );
        if ($this->customArrayFormatterCollection) {
            foreach ($this->customArrayFormatterCollection as $customArrayFormatter) {
                $customArrayFormatter->setTypeFormatter($this);
            }
        }
        $this->customObjectFormatterCollection = (
            $this->customObjectFormatterCollection
            ? clone $this->customObjectFormatterCollection
            : null
        );
        if ($this->customObjectFormatterCollection) {
            foreach ($this->customObjectFormatterCollection as $customObjectFormatter) {
                $customObjectFormatter->setTypeFormatter($this);
            }
        }
        $this->customResourceFormatterCollection = (
            $this->customResourceFormatterCollection
            ? clone $this->customResourceFormatterCollection
            : null
        );
        if ($this->customResourceFormatterCollection) {
            foreach ($this->customResourceFormatterCollection as $customResourceFormatter) {
                $customResourceFormatter->setTypeFormatter($this);
            }
        }
        $this->customStringFormatterCollection = (
            $this->customStringFormatterCollection
            ? clone $this->customStringFormatterCollection
            : null
        );
        if ($this->customStringFormatterCollection) {
            foreach ($this->customStringFormatterCollection as $customStringFormatter) {
                $customStringFormatter->setTypeFormatter($this);
            }
        }
    }

    /**
     * Returns the spelled-out value. E.g. `true` will be output as `true`, strings will be wrapped in quotes (like
     * `"foo"`), etc. To prepend information about the data type, use method `typeCast`.
     *
     * @var mixed $value
     */
    public function cast($value, bool $isSamplifying = true): string
    {
        if (is_null($value)) {
            return "null";
        }
        if (is_bool($value)) {
            return ($value ? "true" : "false");
        }
        if (is_numeric($value)) {
            return strval($value);
        }
        if (is_string($value)) {
            if ($this->customStringFormatterCollection && $this->customStringFormatterCollection->count()) {
                foreach ($this->customStringFormatterCollection as $stringFormatter) {
                    $stringFormatter = $stringFormatter->withIsPrependingType(false);
                    $stringFormatter = $stringFormatter->withIsSamplifying($isSamplifying);
                    $return = $stringFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $stringFormatter = $this->defaultStringFormatter->withIsPrependingType(false);
            $stringFormatter = $stringFormatter->withIsSamplifying($isSamplifying);
            return $stringFormatter->formatEnsureString($value);
        }
        if (is_object($value)) {
            if ($this->customObjectFormatterCollection && $this->customObjectFormatterCollection->count()) {
                foreach ($this->customObjectFormatterCollection as $objectFormatter) {
                    $objectFormatter = $objectFormatter->withIsPrependingType(false);
                    $return = $objectFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $objectFormatter = $this->defaultObjectFormatter->withIsPrependingType(false);
            $objectFormatter = $objectFormatter->withIsSamplifying($isSamplifying);
            return $objectFormatter->formatEnsureString($value);
        }
        if (is_array($value)) {
            if ($this->customArrayFormatterCollection && $this->customArrayFormatterCollection->count()) {
                foreach ($this->customArrayFormatterCollection as $arrayFormatter) {
                    $arrayFormatter = $arrayFormatter->withIsPrependingType(false);
                    $arrayFormatter = $arrayFormatter->withIsSamplifying($isSamplifying);
                    $return = $arrayFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $arrayFormatter = $this->defaultArrayFormatter->withIsPrependingType(false);
            $arrayFormatter = $arrayFormatter->withIsSamplifying($isSamplifying);
            return $arrayFormatter->formatEnsureString($value);
        }
        if (is_resource($value)) {
            if ($this->customResourceFormatterCollection && $this->customResourceFormatterCollection->count()) {
                foreach ($this->customResourceFormatterCollection as $resourceFormatter) {
                    $resourceFormatter = $resourceFormatter->withIsPrependingType(false);
                    $resourceFormatter = $resourceFormatter->withIsSamplifying($isSamplifying);
                    $return = $resourceFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $resourceFormatter = $this->defaultResourceFormatter->withIsPrependingType(false);
            $resourceFormatter = $resourceFormatter->withIsSamplifying($isSamplifying);
            return $resourceFormatter->formatEnsureString($value);
        }
        return $this->maskString(@strval($value));
    }

    /**
     * Escapes backslashes and the quoting character with additional baskslashes.
     */
    public function escape(string $str): string
    {
        $escaped = "\\";
        if ($this->stringQuotingCharacter !== $escaped) {
            $escaped .= $this->stringQuotingCharacter;
        }
        return addcslashes($str, $escaped);
    }

    /**
     * Returns the data type and then the spelled-out value. For just the value, use method `cast`.
     *
     * - For arrays: The size is included, e.g.: "(array(2))"
     * - for strings: The multibyte string length is included, e.g.: "(string(2))"
     *
     * @var mixed $value
     */
    public function typeCast($value, bool $isSamplifying = true): string
    {
        if (is_null($value)) {
            return "(null) null";
        }
        if (is_bool($value)) {
            return "(bool) " . ($value ? "true" : "false");
        }
        if (is_int($value)) {
            return sprintf(
                "(int) %d",
                $value
            );
        }
        if (is_float($value)) {
            return sprintf(
                "(float) %s",
                strval($value)
            );
        }
        if (is_string($value)) {
            if ($this->customStringFormatterCollection && $this->customStringFormatterCollection->count()) {
                foreach ($this->customStringFormatterCollection as $stringFormatter) {
                    $stringFormatter = $stringFormatter->withIsPrependingType(true);
                    $stringFormatter = $stringFormatter->withIsSamplifying($isSamplifying);
                    $return = $stringFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $stringFormatter = $this->defaultStringFormatter->withIsPrependingType(true);
            $stringFormatter = $stringFormatter->withIsSamplifying($isSamplifying);
            return $stringFormatter->formatEnsureString($value);
        }
        if (is_object($value)) {
            if ($this->customObjectFormatterCollection && $this->customObjectFormatterCollection->count()) {
                foreach ($this->customObjectFormatterCollection as $objectFormatter) {
                    $objectFormatter = $objectFormatter->withIsPrependingType(true);
                    $objectFormatter = $objectFormatter->withIsSamplifying($isSamplifying);
                    $return = $customObjectFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $objectFormatter = $this->defaultObjectFormatter->withIsPrependingType(true);
            $objectFormatter = $objectFormatter->withIsSamplifying($isSamplifying);
            return $objectFormatter->formatEnsureString($value);
        }
        if (is_array($value)) {
            if ($this->customArrayFormatterCollection && $this->customArrayFormatterCollection->count()) {
                foreach ($this->customArrayFormatterCollection as $arrayFormatter) {
                    $arrayFormatter = $arrayFormatter->withIsPrependingType(true);
                    $arrayFormatter = $arrayFormatter->withIsSamplifying($isSamplifying);
                    $return = $arrayFormatter->format($value);
                    if (is_string($return)) {
                        return $return;
                    }
                }
            }
            $arrayFormatter = $this->defaultArrayFormatter->withIsPrependingType(true);
            $arrayFormatter = $arrayFormatter->withIsSamplifying($isSamplifying);
            return $arrayFormatter->formatEnsureString($value);
        }
        if (is_resource($value)) {
            if (is_resource($value)) {
                if ($this->customResourceFormatterCollection && $this->customResourceFormatterCollection->count()) {
                    foreach ($this->customResourceFormatterCollection as $resourceFormatter) {
                        $resourceFormatter = $resourceFormatter->withIsPrependingType(true);
                        $resourceFormatter = $resourceFormatter->withIsSamplifying($isSamplifying);
                        $return = $resourceFormatter->format($value);
                        if (is_string($return)) {
                            return $return;
                        }
                    }
                }
                $resourceFormatter = $this->defaultResourceFormatter->withIsPrependingType(true);
                $resourceFormatter = $resourceFormatter->withIsSamplifying($isSamplifying);
                return $resourceFormatter->formatEnsureString($value);
            }
        }
        return sprintf(
            "(%s) %s",
            gettype($value),
            @strval($value)
        );
    }

    public function maskString(string $str): string
    {
        if ($this->maskedEncryptedStringCollection && $this->maskedEncryptedStringCollection->count()) {
            $maskedStrings = array_filter(array_map(function(EncryptedString $encryptedString){
                return $encryptedString->decrypt();
            }, $this->maskedEncryptedStringCollection->toArray()), function(string $s){
                return mb_strlen($s, (string)$this->encoding) > 0;
            });
            if ($maskedStrings) {
                uasort($maskedStrings, function(string $a, string $b){
                    return (mb_strlen($a, (string)$this->encoding) <=> mb_strlen($b, (string)$this->encoding)) * (-1);
                });
                foreach ($maskedStrings as $maskedString) {
                    $str = str_replace($maskedString, static::STRING_MASK, $str);
                }
            }
        }
        return $str;
    }

    /**
     * Escapes a string and wraps it in quoting characters. E.g. 'foo' will become '"foo"'.
     */
    public function quoteAndEscape(string $str): string
    {
        return $this->stringQuotingCharacter . $this->escape($str) . $this->stringQuotingCharacter;
    }

    public function withCustomArrayFormatterCollection(?ArrayFormatterCollection $customArrayFormatterCollection): TypeFormatter
    {
        $clone = clone $this;
        $clone->customArrayFormatterCollection = $customArrayFormatterCollection;
        if ($clone->customArrayFormatterCollection) {
            foreach ($clone->customArrayFormatterCollection as $customArrayFormatter) {
                $customArrayFormatter->setTypeFormatter($clone);
            }
        }
        return $clone;
    }

    public function withCustomObjectFormatterCollection(?ObjectFormatterCollection $customObjectFormatterCollection): TypeFormatter
    {
        $clone = clone $this;
        $clone->customObjectFormatterCollection = $customObjectFormatterCollection;
        if ($clone->customObjectFormatterCollection) {
            foreach ($clone->customObjectFormatterCollection as $customObjectFormatter) {
                $customObjectFormatter->setTypeFormatter($clone);
            }
        }
        return $clone;
    }

    public function withCustomResourceFormatterCollection(?ResourceFormatterCollection $customResourceFormatterCollection): TypeFormatter
    {
        $clone = clone $this;
        $clone->customResourceFormatterCollection = $customResourceFormatterCollection;
        if ($clone->customResourceFormatterCollection) {
            foreach ($clone->customResourceFormatterCollection as $customResourceFormatter) {
                $customResourceFormatter->setTypeFormatter($clone);
            }
        }
        return $clone;
    }

    public function withCustomStringFormatterCollection(?StringFormatterCollection $customStringFormatterCollection): TypeFormatter
    {
        $clone = clone $this;
        $clone->customStringFormatterCollection = $customStringFormatterCollection;
        if ($clone->customStringFormatterCollection) {
            foreach ($clone->customStringFormatterCollection as $customStringFormatter) {
                $customStringFormatter->setTypeFormatter($clone);
            }
        }
        return $clone;
    }

    public function withEncoding(Encoding $encoding): TypeFormatter
    {
        $clone = clone $this;
        $clone->encoding = $encoding;
        return $clone;
    }

    public function withArrayDepthCurrent(int $arrayDepthCurrent): TypeFormatter
    {
        $clone = clone $this;
        $clone->arrayDepthCurrent = max(0, $arrayDepthCurrent);
        return $clone;
    }

    public function withArrayDepthMaximum(int $arrayDepthMaximum): TypeFormatter
    {
        $clone = clone $this;
        $clone->arrayDepthMaximum = max(0, $arrayDepthMaximum);
        return $clone;
    }

    public function withArraySampleSize(int $arraySampleSize): TypeFormatter
    {
        $clone = clone $this;
        $clone->arraySampleSize = max(0, $arraySampleSize);
        return $clone;
    }

    public function withMaskedEncryptedStringCollection(?EncryptedStringCollection $maskedEncryptedStringCollection): TypeFormatter
    {
        $clone = clone $this;
        $clone->maskedEncryptedStringCollection = $maskedEncryptedStringCollection;
        return $clone;
    }

    public function withObjectDepthCurrent(int $objectDepthCurrent): TypeFormatter
    {
        $clone = clone $this;
        $clone->objectDepthCurrent = max(0, $objectDepthCurrent);
        return $clone;
    }

    public function withObjectDepthMaximum(int $objectDepthMaximum): TypeFormatter
    {
        $clone = clone $this;
        $clone->objectDepthMaximum = max(0, $objectDepthMaximum);
        return $clone;
    }

    public function withStringSampleSize(int $stringSampleSize): TypeFormatter
    {
        $clone = clone $this;
        $clone->stringSampleSize = max(0, $stringSampleSize);
        return $clone;
    }

    /**
     * @throws \RuntimeException
     */
    public function withStringQuotingCharacter(string $stringQuotingCharacter): TypeFormatter
    {
        try {
            $clone = clone $this;
            $clone->_setStringQuotingCharacter($stringQuotingCharacter);
        } catch (Throwable $t) {
            throw new \RuntimeException(sprintf(
                "Failed to clone \\%s",
                get_class($this)
            ), 0, $t);
        }
        return $clone;
    }

    public function getArrayDepthCurrent(): int
    {
        return $this->arrayDepthCurrent;
    }

    public function getArrayDepthMaximum(): int
    {
        return $this->arrayDepthMaximum;
    }

    public function getArraySampleSize(): int
    {
        return $this->arraySampleSize;
    }

    public function getDefaultArrayFormatter(): DefaultArrayFormatter
    {
        return $this->defaultArrayFormatter;
    }

    public function getDefaultObjectFormatter(): DefaultObjectFormatter
    {
        return $this->defaultObjectFormatter;
    }

    public function getDefaultResourceFormatter(): DefaultResourceFormatter
    {
        return $this->defaultResourceFormatter;
    }

    public function getDefaultStringFormatter(): DefaultStringFormatter
    {
        return $this->defaultStringFormatter;
    }

    public function getEncoding(): Encoding
    {
        return $this->encoding;
    }

    public function getObjectDepthCurrent(): int
    {
        return $this->objectDepthCurrent;
    }

    public function getObjectDepthMaximum(): int
    {
        return $this->objectDepthMaximum;
    }

    public function getStringSampleSize(): int
    {
        return $this->stringSampleSize;
    }

    public function getStringQuotingCharacter(): string
    {
        return $this->stringQuotingCharacter;
    }

    protected function _setStringQuotingCharacter(string $stringQuotingCharacter): void
    {
        $length = mb_strlen($stringQuotingCharacter, (string)$this->encoding);
        if (1 !== $length) {
            throw new \UnexpectedValueException(sprintf(
                "Argument \$stringQuotingCharacter must be exactly 1 character. Found: %s",
                TypeFormatter::create()->typeCast($stringQuotingCharacter)
            ));
        }
        $this->stringQuotingCharacter = $stringQuotingCharacter;
    }

    public static function create(?Encoding $encoding = null): TypeFormatter
    {
        if (null === $encoding) {
            $encoding = Encoding::getInstance();
        }
        return new static($encoding);
    }

    public static function setDefault(TypeFormatter $typeFormatter): void
    {
        self::$default = $typeFormatter;
    }

    public static function setVariation(string $key, TypeFormatter $typeFormatter): void
    {
        self::$variations[$key] = $typeFormatter;
    }

    public static function getDefault(): TypeFormatter
    {
        if (null === self::$default) {
            self::$default = static::create();
        }
        return self::$default;
    }

    /**
     * @throws \RuntimeException
     */
    public static function getVariation(string $key): TypeFormatter
    {
        if (array_key_exists($key, self::$variations)) {
            return self::$variations[$key];
        }
        throw new \RuntimeException(sprintf(
            "\\%s does not hold a %s variation",
            get_class($this),
            TypeFormatter::create()->cast($key)
        ));
    }
}
