<?php


namespace YZSLAB\PPKI\Commands\Manage;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YZSLAB\PPKI\Contracts\CertificateStorage;

abstract class ExportCertificates extends Command
{
    public function __construct(private CertificateStorage $certificateStorage)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument("name", InputArgument::REQUIRED);
        $this->addOption("cert", null, InputOption::VALUE_OPTIONAL, "", false)
            ->addOption("key", null, InputOption::VALUE_OPTIONAL, "", false)
            ->addOption("ca", null, InputOption::VALUE_OPTIONAL, "", false)
            ->addOption("fullchain", null, InputOption::VALUE_OPTIONAL, "", false)
            ->addOption("concat", null, InputOption::VALUE_OPTIONAL, "Concatenating the key file and the certificate file into a single pem file", false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $certPath = $input->getOption("cert");
        $keyPath = $input->getOption("key");
        $CAPath = $input->getOption("ca");
        $fullchainPath = $input->getOption("fullchain");
        $concatPath = $input->getOption("concat");
        if ($certPath === null) {
            $certPath = "php://output";
        }
        if ($keyPath === null) {
            $keyPath = "php://output";
        }
        if ($CAPath === null) {
            $CAPath = "php://output";
        }
        if ($fullchainPath === null) {
            $fullchainPath = "php://output";
        }
        if ($concatPath === null) {
            $concatPath = "php://output";
        }

        if ($certPath) {
            file_put_contents($certPath, $this->certificateStorage->getCertificate($input->getArgument("name")));
        }
        if ($CAPath) {
            file_put_contents($CAPath, $this->certificateStorage->getCA($input->getArgument("name")));
        }
        if ($fullchainPath) {
            file_put_contents($fullchainPath, $this->certificateStorage->getFullChain($input->getArgument("name")));
        }

        $oldUmask = umask(0077);
        try {
            if ($keyPath) {
                file_put_contents($keyPath, $this->certificateStorage->getPrivateKey($input->getArgument("name")));
            }
            if ($concatPath) {
                $concat = $this->certificateStorage->getPrivateKey($input->getArgument("name"));
                $concat .= $this->certificateStorage->getCertificate($input->getArgument("name"));
                file_put_contents($concatPath, $concat);
            }
        } finally {
            umask($oldUmask);
        }

        return Command::SUCCESS;
    }
}