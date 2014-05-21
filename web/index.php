<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = isset($_GET['debug']);
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__.'/../cache/profiler',
    'profiler.mount_prefix' => '/profiler'
));
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/../silex.log',
));
$app['twig']->addExtension(new Symfony\Bundle\WebProfilerBundle\Twig\WebProfilerExtension());

// Controllers
$app->get('/', function(Silex\Application $app) {
    $app['monolog']->addError("Test");
    return ["test" => "Welcome!", "hello"];
});
$app->post('/test', function() {
    return "Test";
});

$app->on('kernel.exception', function($event) {
    $event->setResponse(new Symfony\Component\HttpFoundation\JsonResponse(array("error" => $event->getException()->getMessage())));
});
$app->on('kernel.view', function($event) {
    $result = $event->getControllerResult();
    if (!is_object($result)) {
        $obj = new stdClass();
        if (is_array($result)) {
            $data = array();
            foreach ($result as $key => $val) {
                if (!$key) {
                    $data[] = $val;
                } else {
                    $obj->$key = $val;
                }
            }
            if ($data) {
                $obj->data = $data;
            }
        } else {
            $obj->result = $result;
        }
        $result = $obj;
    }
    $response = $event->getResponse($result);
    $event->setResponse(new Symfony\Component\HttpFoundation\JsonResponse($result));
});
$app->on('kernel.response', function($event) {
    // $event->getResponse()->headers->set('Content-Type', 'application/json');
});

$app->run();
