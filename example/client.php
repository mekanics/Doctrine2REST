<?php

require '../../../doctrine/trunk/lib/Doctrine/Common/IsolatedClassLoader.php';

$classLoader = new \Doctrine\Common\IsolatedClassLoader('DoctrineExtensions\REST');
$classLoader->setBasePath('../lib');
$classLoader->register();

$url = 'http://localhost/JWageGit/Doctrine2REST/example/server.php';
$client = new \DoctrineExtensions\REST\Client($url, 'jwage', 'password');
$result = $client->dql('SELECT u FROM Entities\User u WHERE u.id = 20');
print_r($result);