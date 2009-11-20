<?php

require '../../../doctrine/trunk/lib/Doctrine/Common/IsolatedClassLoader.php';

$classLoader = new \Doctrine\Common\IsolatedClassLoader('DoctrineExtensions\REST');
$classLoader->setBasePath('../lib');
$classLoader->register();

$client = new \DoctrineExtensions\REST\Client('http://localhost/JWageGit/Doctrine2REST/example/server.php');
$result = $client->dql('SELECT u FROM Entities\User u WHERE u.id = 20');
print_r($result);