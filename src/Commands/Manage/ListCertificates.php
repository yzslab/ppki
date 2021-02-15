<?php


namespace YZSLAB\PPKI\Commands\Manage;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YZSLAB\PPKI\Contracts\CertificateStorage;

abstract class ListCertificates extends Command
{
    public function __construct(private CertificateStorage $CACertificateStorage)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->CACertificateStorage->list() as $item) {
            $parsed = openssl_x509_parse($this->CACertificateStorage->getCertificate($item));
            $output->writeln(sprintf("%s: %s, %s, valid from %s to %s", $item, $parsed["name"], $parsed["signatureTypeSN"], date("Y-m-d H:i:s", intval($parsed["validFrom_time_t"])), date("Y-m-d H:i:s", intval($parsed["validTo_time_t"]))));
        }

        return Command::SUCCESS;
    }
}