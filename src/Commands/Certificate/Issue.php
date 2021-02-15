<?php
declare(strict_types=1);

namespace YZSLAB\PPKI\Commands\Certificate;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YZSLAB\PPKI\Contracts\CertificateStorage;
use YZSLAB\PPKI\OpenSSL\ConfigurationBuilder;

class Issue extends Certificate
{
    protected static $defaultName = 'cert:issue';

    public function __construct(CertificateStorage $CACACertificateStorage, private CertificateStorage $certificateStorage)
    {
        parent::__construct($CACACertificateStorage);
    }

    protected function extraConfigure()
    {
    }

    protected function CSRConfigure(InputInterface $input, OutputInterface $output, ConfigurationBuilder $configurationBuilder)
    {
    }

    protected function X509Configure(InputInterface $input, OutputInterface $output, ConfigurationBuilder $configurationBuilder)
    {
        $configurationBuilder
            ->setIsCA(false)
            ->addX509Ext("extendedKeyUsage", "serverAuth, clientAuth, codeSigning, emailProtection")
            ->addX509Ext("subjectKeyIdentifier", "hash")
            ->addX509Ext("authorityKeyIdentifier", "keyid,issuer")
            ->addKeyUsages("nonRepudiation")
            ->addKeyUsages("digitalSignature")
            ->addKeyUsages("keyEncipherment");
    }

    protected function configure()
    {
        $this->addArgument("name", InputArgument::REQUIRED)
            ->addArgument("commonName", InputArgument::REQUIRED);

        $this
            ->addOption("ca-name", null, InputOption::VALUE_REQUIRED)
            ->addOption("ecc", null, InputOption::VALUE_NONE)
            ->addOption("days", null, InputOption::VALUE_REQUIRED, "", 365)
            ->addOption("dns-name", null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption("ip-address", null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption("override", null, InputOption::VALUE_NONE)
            ->addOption("csr-config", null, InputOption::VALUE_REQUIRED)
            ->addOption("x509-config", null, InputOption::VALUE_REQUIRED);
    }

    protected function getCertificateStorage(): CertificateStorage
    {
        return $this->certificateStorage;
    }
}