<?php
declare(strict_types=1);

namespace YZSLAB\PPKI\OpenSSL;


class ConfigurationBuilder
{
    private bool $isCA = false;

    private array $keyUsages = [];

    private array $DNSNames = [];

    private array $IPAddresses = [];

    private array $reqExt = [];

    private array $x509Ext = [];

    public function setIsCA(bool $value)
    {
        $this->isCA = $value;
        return $this;
    }

    public function addKeyUsages(string $keyUsage)
    {
        $this->keyUsages[$keyUsage] = true;
        return $this;
    }

    public function addDNSName(string $DNSName)
    {
        $this->DNSNames[$DNSName] = true;
        return $this;
    }

    public function addIPAddress(string $IPAddress)
    {
        $this->IPAddresses[$IPAddress] = true;
        return $this;
    }

    public function addReqExt($name, $value)
    {
        $this->reqExt[$name] = $value;
        return $this;
    }

    public function addX509Ext($name, $value)
    {
        $this->x509Ext[$name] = $value;
        return $this;
    }

    public function buildReq(): string
    {
        $reqExtContents = $this->buildReqExt();
        $baseConfiguration = "[req]
distinguished_name = req_distinguished_name
req_extensions = v3_req
 
[req_distinguished_name]
";
        if (empty($reqExtContents)) {
            $baseConfiguration = "[req]
distinguished_name = req_distinguished_name
 
[req_distinguished_name]
";
        }


        return $baseConfiguration . $reqExtContents;
    }

    public function buildX509(): string
    {
        $baseConfiguration = "[req]
x509_extensions	= v3_x509
";


        return $baseConfiguration . $this->buildX509Ext();
    }

    public function exportReq2File(string $path = null): string
    {
        if (is_null($path)) {
            $path = self::createTemporaryFilePath();
        }
        $oldUmask = umask(0077);
        try {
            file_put_contents($path, $this->buildReq());
        } finally {
            umask($oldUmask);
        }
        return $path;
    }

    public function exportX5092File(string $path = null): string
    {
        if (is_null($path)) {
            $path = self::createTemporaryFilePath();
        }
        $oldUmask = umask(0077);
        try {
            file_put_contents($path, $this->buildX509());
        } finally {
            umask($oldUmask);
        }
        return $path;
    }

    public function hasSAN(): bool
    {
        return !!(count($this->DNSNames) || count($this->IPAddresses));
    }

    private function buildReqExt(): string
    {
        if (count($this->reqExt) || $this->hasSAN()) {
            $reqExtContents = "[v3_req]\n";
            foreach ($this->reqExt as $key => $value) {
                $reqExtContents .= $key . " = " . $value . "\n";
            }
            return $reqExtContents . $this->buildSAN();
        }
        return "";
    }

    private function buildX509Ext(): string
    {
        $x509ExtContents = "[v3_x509]\n";
        foreach ($this->x509Ext as $key => $value) {
            $x509ExtContents .= $key . " = " . $value . "\n";
        }
        $x509ExtContents .= "basicConstraints = CA:" . ($this->isCA ? "TRUE" : "FALSE") ."\n";
        if (count($this->keyUsages)) {
            $x509ExtContents .= "keyUsage = " . implode(", ", array_keys($this->keyUsages)) . "\n";
        }
        return $x509ExtContents . $this->buildSAN();
    }

    private function buildSAN(): string
    {
        $SANContents = "";
        if ($this->hasSAN()) {
            $sanContents = "subjectAltName = @alt_names\n\n[alt_names]";
            foreach (array_keys($this->DNSNames) as $key => $value) {
                $sanContents .= "\nDNS." . $key . " = " . $value;
            }
            foreach (array_keys($this->IPAddresses) as $key => $value) {
                $sanContents .= "\nIP." . $key . " = " . $value;
            }
            $SANContents = $sanContents . "\n";
        }
        return $SANContents;
    }

    private function createTemporaryFilePath(): string
    {
        return sprintf("%s/%s.%s.%s.conf", __DIR__ . "/../../ssl/config", "openssl", mt_rand(1000000, 9999999), time());
    }
}