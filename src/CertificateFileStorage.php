<?php
declare(strict_types=1);

namespace YZSLAB\PPKI;


class CertificateFileStorage implements \YZSLAB\PPKI\Contracts\CertificateStorage
{
    public function __construct(private string $certificateStorePath)
    {
    }

    public function isCertificateExists(string $name): bool
    {
        return file_exists($this->getCertificatePath($name));
    }

    public function store(string $name, $certificate, $privateKey, string $issuers = null)
    {
        $oldUmask = umask(0077);
        try {
            $certificatePath = $this->getCertificatePath($name);
            $privateKeyPath = $this->getPrivateKeyPath($name);
            @mkdir(dirname($certificatePath), 0700, true);

            // Store certificate and private key
            if (is_string($certificate)) {
                file_put_contents($certificatePath, $certificate);
            } else {
                openssl_x509_export_to_file($certificate, $certificatePath);
            }
            if (is_string($privateKey)) {
                file_put_contents($privateKeyPath, $privateKey);
            } else {
                openssl_pkey_export_to_file($privateKey, $privateKeyPath);
            }

            // Write full chain
            $fullChainFilePath = $this->getFullChainPath($name);
            @unlink($fullChainFilePath);
            if ($issuers) {
                file_put_contents($this->getCAPath($name), $issuers);
                file_put_contents($fullChainFilePath, file_get_contents($certificatePath));
                file_put_contents($fullChainFilePath, $issuers, FILE_APPEND);
            }
        } finally {
            umask($oldUmask);
        }
    }

    public function getCertificate(string $name): string
    {
        return file_get_contents($this->getCertificatePath($name));
    }

    public function getPrivateKey(string $name): string
    {
        return file_get_contents($this->getPrivateKeyPath($name));
    }

    public function getCA(string $name): string
    {
        $CAPath = $this->getCAPath($name);
        if (file_exists($CAPath)) {
            return file_get_contents($CAPath);
        }
        $certificatePath = $this->getCertificatePath($name);
        return file_get_contents($certificatePath);
    }

    public function getFullChain(string $name): string
    {
        $fullChainPath = $this->getFullChainPath($name);
        if (file_exists($fullChainPath)) {
            return file_get_contents($fullChainPath);
        }
        $certificatePath = $this->getCertificatePath($name);
        return file_get_contents($certificatePath);
    }

    public function list(): array
    {
        $list = [];
        $items = scandir($this->certificateStorePath);
        foreach ($items as $item) {
            if (is_dir($this->certificateStorePath) && file_exists($this->getCertificatePath($item))) {
                $list[] = $item;
            }
        }
        return $list;
    }

    public function delete(string $name)
    {
        $baseDirectory = $this->getBaseDirectory($name);
        foreach (scandir($baseDirectory) as $item) {
            $fullPath = $baseDirectory . "/" . $item;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
        rmdir($baseDirectory);
    }

    private function getBaseDirectory(string $name)
    {
        return $this->certificateStorePath . "/" . $name;
    }

    private function getCertificatePath(string $name): string
    {
        return $this->getBaseDirectory($name) . "/cert.crt";
    }

    private function getPrivateKeyPath(string $name): string
    {
        return $this->getBaseDirectory($name) . "/private.key";
    }

    private function getCAPath(string $name): string
    {
        return $this->getBaseDirectory($name) . "/ca.pem";
    }

    private function getFullChainPath(string $name): string
    {
        return $this->getBaseDirectory($name) . "/fullchain.pem";
    }
}