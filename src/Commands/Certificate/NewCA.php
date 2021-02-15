<?php
declare(strict_types=1);

namespace YZSLAB\PPKI\Commands\Certificate;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YZSLAB\PPKI\OpenSSL\ConfigurationBuilder;

class NewCA extends Certificate
{
    protected static $defaultName = 'cert:ca:new';

    protected function extraConfigure()
    {
    }

    protected function CSRConfigure(InputInterface $input, OutputInterface $output, ConfigurationBuilder $configurationBuilder)
    {
    }

    protected function X509Configure(InputInterface $input, OutputInterface $output, ConfigurationBuilder $configurationBuilder)
    {
        $configurationBuilder
            ->setIsCA(true)
            ->addX509Ext("extendedKeyUsage", "serverAuth, clientAuth, codeSigning, emailProtection")
            ->addX509Ext("subjectKeyIdentifier", "hash")
            ->addX509Ext("authorityKeyIdentifier", "keyid:always,issuer:always")
            ->addKeyUsages("keyCertSign")
            ->addKeyUsages("digitalSignature")
            ->addKeyUsages("cRLSign");
    }
}