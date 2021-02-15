<?php


namespace YZSLAB\PPKI\OpenSSL;


use YZSLAB\PPKI\Exceptions\OpenSSLException;

abstract class OpenSSL
{
    public static function parseX509(string $x509)
    {
        return self::returnValueCheck(openssl_x509_read($x509));
    }

    public static function selfSignCSR($csr, $privateKey, int $days, $options = null, $serial = 0)
    {
        return self::returnValueCheck(openssl_csr_sign($csr, null, $privateKey, $days, $options, $serial));
    }

    public static function signCSR($csr, $CACertificate, $privateKey, int $days, $options = null, $serial = 0)
    {
        return self::returnValueCheck(openssl_csr_sign($csr, $CACertificate, $privateKey, $days, $options, $serial));
    }

    public static function newCSR(array $distinguishedNames = null, $privateKey, array $options = null, array $extraAttributes = null)
    {
        return self::returnValueCheck(openssl_csr_new($distinguishedNames, $privateKey, $options, $extraAttributes));
    }

    /**
     * @param int $keyBits
     * @return false|resource
     */
    public static function newRSAPrivateKey($keyBits = 2048)
    {
        return self::newPrivateKey([
            "private_key_bits" => $keyBits,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);
    }

    public static function newECPrivateKey($curveName = "prime256v1")
    {
        return self::newPrivateKey([
            "private_key_type" => OPENSSL_KEYTYPE_EC,
            "curve_name" => $curveName,
        ]);
    }

    public static function newPrivateKey(array $configargs = null)
    {
        return self::returnValueCheck(openssl_pkey_new($configargs));
    }

    public static function generateSerialNumber()
    {
        return intval(time() . mt_rand(10000, 99999));
    }

    private static function serialNumberEncode($serialNumber)
    {
        return strtoupper(base_convert($serialNumber, 10, 36));
    }

    private static function returnValueCheck($value)
    {
        if ($value === false) {
            throw new OpenSSLException(openssl_error_string());
        }
        return $value;
    }
}