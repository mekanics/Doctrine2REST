<?php

require '../../../doctrine/trunk/lib/Doctrine/Common/IsolatedClassLoader.php';

$classLoader = new \Doctrine\Common\IsolatedClassLoader('Doctrine');
$classLoader->setBasePath('../../../doctrine/trunk/lib');
$classLoader->register();

$classLoader = new \Doctrine\Common\IsolatedClassLoader('DoctrineExtensions\REST');
$classLoader->setBasePath('../lib');
$classLoader->register();

$classLoader = new \Doctrine\Common\IsolatedClassLoader('Entities');
$classLoader->setBasePath(__DIR__);
$classLoader->register();

$config = new \Doctrine\ORM\Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setProxyDir('/tmp');
$config->setProxyNamespace('Proxies');

$connectionOptions = array(
    'driver' => 'pdo_sqlite',
    'path' => 'database.sqlite'
);

$em = new \DoctrineExtensions\REST\EntityManager\Wrapper(
    \Doctrine\ORM\EntityManager::create($connectionOptions, $config)
);

$server = new \DoctrineExtensions\REST\Server($em, $_REQUEST);
$server->setUsername('jwage');
$server->setPassword('password');
$server->run();