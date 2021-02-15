<?php
declare(strict_types=1);

namespace YZSLAB\PPKI\Contracts;


interface CertificateStorage
{
    public function isCertificateExists(string $name): bool;

    /**
     * @param string $name
     * @param string|resource $certificate
     * @param string|resource $privateKey
     * @param string|null $issuers
     * @return mixed
     */
    public function store(string $name, $certificate, $privateKey, string $issuers = null);

    public function getCertificate(string $name): string;

    public function getPrivateKey(string $name): string;

    public function getCA(string $name): string;

    public function getFullChain(string $name): string;

    public function list(): array;

    public function delete(string $name);
}