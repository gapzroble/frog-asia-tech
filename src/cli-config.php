<?php

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;

$app = require_once __DIR__ . '/app.php';

return new HelperSet(array(
    'db' => new ConnectionHelper($app['db']),
    'em' => new EntityManagerHelper($app['orm.em'])
));
