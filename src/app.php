<?php

use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Kilte\Silex\Pagination\PaginationServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

require_once __DIR__.'/vendor/autoload.php'; 

$app = new Application();
$app['debug'] = true;

$app->register(new UrlGeneratorServiceProvider());

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('project', 'Frog Asia');
    $twig->addGlobal('date_format', 'd.m.Y');
    return $twig;
}));

$app->register(new DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/app/data.db',
    ),
));
$app->register(new DoctrineOrmServiceProvider, array(
    'orm.em.options' => array(
        'mappings' => array(
            array(
                'type' => 'annotation',
                'namespace' => 'FrogAsia',
                'path' => __DIR__.'/app',
            ),
        ),
    ),
));

$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'locale_fallbacks' => array('en'),
));

$app->register(new SessionServiceProvider());

$app->register(new PaginationServiceProvider(), array(
    'pagination.per_page' => 10
));

$app->mount('/', include __DIR__.'/app/controller.php');

return $app;
