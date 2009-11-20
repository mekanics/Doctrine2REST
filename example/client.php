<?php

require '../../../doctrine/trunk/lib/Doctrine/Common/IsolatedClassLoader.php';

$classLoader = new \Doctrine\Common\IsolatedClassLoader('DoctrineExtensions\REST');
$classLoader->setBasePath('../lib');
$classLoader->register();

$client = new \DoctrineExtensions\REST\Client('http://localhost/JWageGit/Doctrine2REST/example/server.php');
print_r($client->list('Entities\User', array('username' => 'jwage')));