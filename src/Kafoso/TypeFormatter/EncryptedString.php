<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter;

/**
 * Stores a string, e.g. a password, as an encrypted value.
 * Used with TypeFormatter to prevent outputting of sensitive information.
 */
class EncryptedString
{
    const ENCRYPTION_METHOD = "AES256";

    /**
     * @var string
     */
    protected $initializationVectorBase;

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var string
     */
    protected $encryptedString;

    public function __construct(string $string, ?string $salt = null)
    {
        if (null === $salt) {
            $salt = static::generateRandomSalt();
        }
        $this->salt = $salt;
        $this->initializationVectorBase = self::generateRandomSalt() . \spl_object_hash($this);
        $this->encryptedString = \openssl_encrypt(
            $string,
            static::ENCRYPTION_METHOD,
            $this->salt,
            0,
            $this->_getInitializationVector()
        );
    }

    /**
     * Decrypts the value, making it readable in clear text (memory) yet again, and returns it.
     */
    public function decrypt(): string
    {
        return \openssl_decrypt(
            $this->encryptedString,
            static::ENCRYPTION_METHOD,
            $this->salt,
            0,
            $this->_getInitializationVector()
        );
    }

    protected function _getInitializationVector(): string
    {
        return substr(\hash("sha256", $this->initializationVectorBase), 0, 16);
    }

    public static function generateRandomSalt(): string
    {
        return \bin2hex(\random_bytes(64));
    }
}
