<?php


namespace YZSLAB\PPKI\Commands\Manage;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use YZSLAB\PPKI\Contracts\CertificateStorage;

abstract class DeleteCertificates extends Command
{
    public function __construct(private CertificateStorage $certificateStorage)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument("name");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->certificateStorage->delete($input->getArgument("name"));
        return Command::SUCCESS;
    }
}