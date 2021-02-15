<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-28
 * Time: 上午1:57
 */

namespace YZSLAB\PPKI\Commands\Certificate;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YZSLAB\PPKI\Contracts\CertificateStorage;
use YZSLAB\PPKI\OpenSSL\ConfigurationBuilder;
use YZSLAB\PPKI\OpenSSL\OpenSSL;

abstract class Certificate extends Command
{
    public function __construct(private CertificateStorage $CACertificateStorage)
    {
        parent::__construct();
    }

    abstract protected function extraConfigure();

    abstract protected function CSRConfigure(InputInterface $input, OutputInterface $output, ConfigurationBuilder $configurationBuilder);

    abstract protected function X509Configure(InputInterface $input, OutputInterface $output, ConfigurationBuilder $configurationBuilder);

    protected function getCertificateStorage(): CertificateStorage
    {
        return $this->CACertificateStorage;
    }

    protected function configure()
    {
        $this->addArgument("name", InputArgument::REQUIRED)
            ->addArgument("commonName", InputArgument::REQUIRED);

        $this
            ->addOption("ca-name", null, InputOption::VALUE_REQUIRED)
            ->addOption("ecc", null, InputOption::VALUE_NONE)
            ->addOption("days", null, InputOption::VALUE_REQUIRED, "", 365)
            ->addOption("override", null, InputOption::VALUE_NONE)
            ->addOption("csr-config", null, InputOption::VALUE_REQUIRED)
            ->addOption("x509-config", null, InputOption::VALUE_REQUIRED)
            ->addOption("dns-name", null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY)
            ->addOption("ip-address", null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY);

        $this->extraConfigure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Key exists check
        if ($this->CACertificateStorage->isCertificateExists($input->getArgument("name")) && !$input->getOption("override")) {
            $output->writeln("Certificate " . $input->getArgument("name") . " already exists, add --override option override it");
            return Command::FAILURE;
        }

        // Create private key
        $privateKey = null;
        if ($input->getOption("ecc")) {
            $privateKey = OpenSSL::newECPrivateKey();
        } else {
            $privateKey = OpenSSL::newRSAPrivateKey();
        }

        // Allow custom OpenSSL configuration
        $configFilePath = $input->getOption("csr-config");
        if (empty($configFilePath)) {
            $configurationBuilder = new ConfigurationBuilder();
            $this->CSRConfigure($input, $output, $configurationBuilder);
            $configFilePath = $configurationBuilder->exportReq2File();
        }
        // Generate CSR
        $csr = OpenSSL::newCSR([
            "commonName" => $input->getArgument("commonName"),
        ], $privateKey, [
            "config" => $configFilePath,
            "digest_alg" => "sha256",
        ]);

        // Allow custom OpenSSL configuration
        $configFilePath = $input->getOption("x509-config");
        if (empty($configFilePath)) {
            $configurationBuilder = new ConfigurationBuilder();
            $this->X509Configure($input, $output, $configurationBuilder);
            foreach ($input->getOption("dns-name") as $dnsName) {
                $configurationBuilder->addDNSName($dnsName);
            }
            foreach ($input->getOption("ip-address") as $IPAddress) {
                $configurationBuilder->addIPAddress($IPAddress);
            }
            $configFilePath = $configurationBuilder->exportX5092File();
        }

        // Sign CSR
        $CSRConfig = [
            "config" => $configFilePath,
            "digest_alg" => "sha256",
        ];
        if ($input->getOption("ca-name")) {
            $x509 = OpenSSL::signCSR($csr, $this->CACertificateStorage->getCertificate($input->getOption("ca-name")), $this->CACertificateStorage->getPrivateKey($input->getOption("ca-name")), intval($input->getOption("days")), $CSRConfig, OpenSSL::generateSerialNumber());
        } else {
            $x509 = OpenSSL::selfSignCSR($csr, $privateKey, intval($input->getOption("days")), $CSRConfig, OpenSSL::generateSerialNumber());
        }

        // Store certificate and private key
        $this->getCertificateStorage()->store($input->getArgument("name"), $x509, $privateKey, $input->getOption("ca-name") ? $this->CACertificateStorage->getFullChain($input->getOption("ca-name")) : null);
        $output->writeln("Certificate stored");

        return Command::SUCCESS;
    }
}