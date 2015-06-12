<?php

use Doctrine\ORM\Tools\Pagination\Paginator;
use FrogAsia\Frog;
use FrogAsia\Pond;
use Symfony\Component\HttpFoundation\Request;

$routes = $app['controllers_factory'];

$routes->get('/', function() use ($app) {
    return $app->redirect($app['url_generator']->generate('frogs'));
})
->bind('homepage');

$routes->get('/frogs', function(Request $request) use ($app) {
    $status = $request->get('status');
    $gender = $request->get('gender');
    $page = $request->get('page', 1);
    $sort = $request->get('sort', 'name');
    $dir = $request->get('dir', 'asc');
    $query = $app['orm.em']->getRepository('FrogAsia\Frog')->findAll($status, $gender, $page, $sort, $dir);
    $paginator = new Paginator($query);
    
    $pagination = $app['pagination'](count($paginator), $page);
    $pages = $pagination->build();
            
    return $app['twig']->render('index.twig', array(
        'frogs' => $paginator,
        'pages' => $pages,
        'current' => $pagination->currentPage(),
    ));
})
->bind('frogs');

// add/edit
$routes->match('/form/{id}', function(Request $request, $id) use ($app) {
    $em = $app['orm.em'];
    
    if ($id) {
        $frog = $em->getRepository('FrogAsia\Frog')->find($id);
        if (!$frog) {
            return $app->redirect($app['url_generator']->generate('frogs'));
        } elseif ($frog->isDead()) {
            $app['session']->getFlashBag()->add('warning', 'You can only view dead frog, OK?');
            return $app->redirect($app['url_generator']->generate('frog', ['id' => $id]));
        }
    } else {
        $frog = new Frog();
    }
    
    $form = $app['form.factory']->createBuilder('form', $frog)
        ->add('name', null, [
            'attr' => ['class' => 'form-control'],
            'label_attr' => ['class' => 'col-sm-2 control-label'],
        ])
        ->add('gender', 'choice', array(
            'choices' => Frog::genders(),
            'expanded' => true,
            'label_attr' => ['class' => 'col-sm-2 control-label'],
//            'disabled' => $frog->getId() !== null,
        ))
        ->getForm();
    
    try {
        $form->handleRequest($request);
    } catch(Exception $e) {
        $app['session']->getFlashBag()->add('error', $e->getMessage());
    }
    
    if ($form->isValid()) {
        $em->persist($frog);
        $em->flush();
        
        $message = $id ? 'Frog has been updated.' : 'Frog has been created.';
        $app['session']->getFlashBag()->add('success', $message);
        
        return $app->redirect($app['url_generator']->generate('frogs'));
    }

    return $app['twig']->render('form.twig', array(
        'form' => $form->createView(),
        'frog' => $frog,
    ));
})
->value('id', null)
->bind('form');

// show
$routes->match('/frog/{id}', function($id) use ($app) {
    $em = $app['orm.em'];
    $frog = $em->getRepository('FrogAsia\Frog')->find($id);
    if (!$frog) {
        $app['session']->getFlashBag()->add('warning', 'Frog not found.');
        return $app->redirect($app['url_generator']->generate('frogs'));
    }
    return $app['twig']->render('frog.twig', array(
        'frog' => $frog,
    ));
})
->bind('frog');

// clone
$routes->match('/clone/{id}', function($id) use ($app) {
    $em = $app['orm.em'];
    $frog = $em->getRepository('FrogAsia\Frog')->find($id);
    if ($frog) {
        if ($frog->isDead()) {
            $app['session']->getFlashBag()->add('warning', 'Dead frog is hard to clone so don\'t bother.');
            return $app->redirect($app['url_generator']->generate('frogs'));
        }
        $copy = clone $frog;
        $em->persist($copy);
        $em->flush();
        
        $app['session']->getFlashBag()->add('success', sprintf('%s has been cloned.', $frog));
    } else {
        $app['session']->getFlashBag()->add('warning', 'Frog not found.');
    }
    return $app->redirect($app['url_generator']->generate('frogs'));
})
->bind('clone');

// kill
$routes->match('/kill/{id}', function($id) use ($app) {
    $em = $app['orm.em'];
    $frog = $em->getRepository('FrogAsia\Frog')->find($id);
    if ($frog) {
        if ($frog->isDead()) {
            $g = $frog->isFemale() ? 'her' : 'him';
            $x = $frog->isFemale() ? 'her' : 'his';
            $app['session']->getFlashBag()->add('warning', sprintf('%s is already dead, forgive %s now or at least pray for %s soul.', $frog->getName(), $g, $x));
        } else {
            $frog->kill();
            $em->persist($frog);
            $em->flush();

            $app['session']->getFlashBag()->add('warning', sprintf('%s has been killed.', $frog->getName()));
        }
    } else {
        $app['session']->getFlashBag()->add('warning', 'Really, trying to kill non-existing frog?');
    }
    return $app->redirect($app['url_generator']->generate('frogs'));
})
->bind('kill');

// mate
$routes->match('/mate/{id}', function(Request $request, $id) use ($app) {
    $em = $app['orm.em'];
    
    $frog = $em->getRepository('FrogAsia\Frog')->find($id);
    if (!$frog) {
        return $app->redirect($app['url_generator']->generate('frogs'));
    } elseif ($frog->isDead()) {
        $app['session']->getFlashBag()->add('warning', 'No you can\'t mate a dead frog.');
        return $app->redirect($app['url_generator']->generate('frogs'));
    }
    
    $choices = [];
    $frogs = $em->getRepository('FrogAsia\Frog')->findAllAlive();
    foreach ($frogs as $f) {
        $choices[$f->getId()] = sprintf('%s [%s]', $f, $f->getGender()[0]);
    }
    
    $form = $app['form.factory']->createBuilder('form', [])
        ->add('partner', 'choice', [
            'choices' => $choices,
            'label_attr' => ['class' => 'col-sm-2 control-label'],
            'attr' => ['class' => 'form-control'],
        ])
        ->getForm();
    
    try {
        $form->handleRequest($request);
    } catch(Exception $e) {
        $app['session']->getFlashBag()->add('error', $e->getMessage());
    }
    
    if ($form->isValid()) {
        $partner = $em->getRepository('FrogAsia\Frog')->find($form->getData()['partner']);
        if ($partner) {
            try {
                $children = get_pond($app)->mate($frog, $partner);
                if (count($children) == 0) {
                    $app['session']->getFlashBag()->add('warning', 'Tried mating but only had fun, wanna try again?');
                } else {
                    foreach ($children as $c) {
                        $em->persist($c);
                    }
                    $em->flush();

                    $app['session']->getFlashBag()->add('success', sprintf('Mating is successful with %d children :)', count($children)));
                    return $app->redirect($app['url_generator']->generate('frogs'));
                }
            } catch(Exception $e) {
                $app['session']->getFlashBag()->add('error', $e->getMessage());
            }
        }
    }

    return $app['twig']->render('mate.twig', array(
        'form' => $form->createView(),
        'frog' => $frog,
    ));
})
->bind('mate');

function get_pond($app) {
    $em = $app['orm.em'];
    $pond = $em->getRepository('FrogAsia\Pond')->findOneBy([]);
    if (!$pond) {
        $pond = new Pond();
        $em->persist($pond);
        $em->flush();
    }
    return $pond;
}

// pond
$routes->match('/pond', function(Request $request) use ($app) {
    $pond = get_pond($app);
    
    $form = $app['form.factory']->createBuilder('form', $pond)
        ->add('condition', 'choice', [
            'choices' => Pond::conditions(),
            'label_attr' => ['class' => 'col-sm-2 control-label'],
            'expanded' => true,
        ])
        ->getForm();
    
    try {
        $form->handleRequest($request);
    } catch(Exception $e) {
        $app['session']->getFlashBag()->add('error', $e->getMessage());
    }
    
    if ($form->isValid()) {
        $em = $app['orm.em'];
        $em->persist($pond);
        $em->flush();
        
        $app['session']->getFlashBag()->add('success', 'Pond setting has been updated.');
        
        return $app->redirect($app['url_generator']->generate('frogs'));
    }

    return $app['twig']->render('pond.twig', array(
        'form' => $form->createView(),
        'pond' => $pond,
    ));
})
->bind('pond');

// random
$routes->match('/random', function(Request $request) use ($app) {
    $frogs = get_pond($app)->createFrogs();
    if (count($frogs) == 0) {
        $app['session']->getFlashBag()->add('warning', 'Due to poor Pond condition, no frog is created.');
    } else {
        $em = $app['orm.em'];
        foreach ($frogs as $c) {
            $em->persist($c);
        }
        $em->flush();
        $app['session']->getFlashBag()->add('success', sprintf('Created %d frogs :)', count($frogs)));
    }
    return $app->redirect($app['url_generator']->generate('frogs'));
})
->bind('random');

return $routes;
