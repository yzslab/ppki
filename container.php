<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;

$containerBuilder = new ContainerBuilder();

$containerBuilder
    ->register("CACertificateStorage", YZSLAB\PPKI\CertificateFileStorage::class)
    ->addArgument(__DIR__ . "/ssl/cacerts");
$containerBuilder
    ->register("CertificateStorage", YZSLAB\PPKI\CertificateFileStorage::class)
    ->addArgument(__DIR__ . "/ssl/certs");
$containerBuilder->register("NewCA", YZSLAB\PPKI\Commands\Certificate\NewCA::class)
    ->addArgument($containerBuilder->get("CACertificateStorage"))
    ->setPublic(true);
$containerBuilder->register("CertIssue", YZSLAB\PPKI\Commands\Certificate\Issue::class)
    ->addArgument($containerBuilder->get("CACertificateStorage"))
    ->addArgument($containerBuilder->get("CertificateStorage"))
    ->setPublic(true);

$containerBuilder->register("CACertList", YZSLAB\PPKI\Commands\Manage\ListCACertificate::class)
    ->addArgument($containerBuilder->get("CACertificateStorage"))
    ->setPublic(true);
$containerBuilder->register("CertList", YZSLAB\PPKI\Commands\Manage\ListCertificate::class)
    ->addArgument($containerBuilder->get("CertificateStorage"))
    ->setPublic(true);
$containerBuilder->register("CACertExport", YZSLAB\PPKI\Commands\Manage\ExportCACertificate::class)
    ->addArgument($containerBuilder->get("CACertificateStorage"))
    ->setPublic(true);
$containerBuilder->register("CertExport", YZSLAB\PPKI\Commands\Manage\ExportCertificate::class)
    ->addArgument($containerBuilder->get("CertificateStorage"))
    ->setPublic(true);
$containerBuilder->register("CACertDelete", YZSLAB\PPKI\Commands\Manage\DeleteCACertificate::class)
    ->addArgument($containerBuilder->get("CACertificateStorage"))
    ->setPublic(true);
$containerBuilder->register("CertDelete", YZSLAB\PPKI\Commands\Manage\DeleteCertificate::class)
    ->addArgument($containerBuilder->get("CertificateStorage"))
    ->setPublic(true);

$containerBuilder->compile();

return $containerBuilder;