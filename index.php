<?php

require __DIR__.'/vendor/autoload.php';


use Jaeger\Config as JaegerConfig;
use OpenTracing\GlobalTracer;
use PDFfiller\OpenTracing\Tag\StringTag;
use PDFfiller\OpenTracing\TracerBridge;


/**
 * An array of options to set to the Jaeger Client.
 *
 * The `$logger` parameter below is optional.
 *
 * @var \Psr\Log\LoggerInterface $logger
 */
$config = [
    'sampler' => [
        'type' => 'const',
        'param' => true,
    ],
    'logging' => true,
    'local_agent' => [
        'reporting_host' => 'localhost',
        'reporting_port' => 5775
    ]
];

$serviceName = 'airslate.addons';

register_shutdown_function(function () {
    \PDFfiller\OpenTracing\GlobalTracerBridge::get()->finish();
});


try {
    $jaeger = new JaegerConfig($config, $serviceName, new \Psr\Log\NullLogger());
    $jaegerClient = $jaeger->initializeTracer();

    \PDFfiller\OpenTracing\GlobalTracerBridge::set(new TracerBridge(GlobalTracer::get()));

    $tracer = \PDFfiller\OpenTracing\GlobalTracerBridge::get();

    $tracer->initHttp();

    /** @var \PDFfiller\OpenTracing\TracerBridgeInterface $tracer */
    $span = $tracer
        ->start('authorization1')
        ->addTag(new StringTag('username', 'some-username'));

    sleep(2);

    $span->finish();/** @var \PDFfiller\OpenTracing\TracerBridgeInterface $tracer */


    $span = $tracer
        ->start('authorization2')
        ->addTag(new StringTag('username', 'some-username'));

    sleep(2);


    $span->finish();

    echo "<pre>";
    print_r($tracer);
    echo "</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}

