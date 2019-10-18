<?php

use Amp\Artax\Client;
use Amp\Artax\HttpSocketPool;
use Amp\Artax\Request;
use Amp\ByteStream\ResourceOutputStream;
use Amp\CancellationTokenSource;
use Amp\Http\Server\Response;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Docker\API\Model\EventsGetResponse200;
use Monolog\Logger;
use Docker\API\Normalizer\EventsGetResponse200ActorNormalizer;
use Docker\API\Normalizer\EventsGetResponse200Normalizer;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Amp\Socket\StaticSocketPool;
use Symfony\Component\Serializer\Serializer;

require __DIR__ . '/vendor/autoload.php';


sleep(1); // wait official /docker-entrypoint.sh started

$containers = [];


if(file_exists("/etc/phpmyadmin/config.inc.php")) {
    file_put_contents("/etc/phpmyadmin/config.autoconfig.inc.php","");
    
    if(strpos(file_get_contents("/etc/phpmyadmin/config.inc.php") , "/etc/phpmyadmin/config.autoconfig.inc.php") === false) {
        $append_data =
<<<EOF

/* Include autoconfig  */
if (file_exists('/etc/phpmyadmin/config.autoconfig.inc.php')) {
    include('/etc/phpmyadmin/config.autoconfig.inc.php');
}
     
EOF;
        file_put_contents("/etc/phpmyadmin/config.inc.php", $append_data, FILE_APPEND);
    }
}


function modify_config_file($containers) {
    if (file_exists('/etc/phpmyadmin/config.autoconfig.inc.php')) {
        $data = 
<<<EOF
<?php
if(!isset(\$i)) {
    \$i=0;
}


EOF;
        foreach($containers as $id => $container) {
            $data .= "\$i++;\n";
            $cfg = $container['cfg'];
            foreach($cfg as $k=>$v) {
                if( strtolower($v) === "true") {
                    $v = "true";
                } else {
                    $v = "\"{$v}\"";
                }
                $data .= "\$cfg['Servers'][\$i][\"{$k}\"]=$v;\n";
            }

        }
        file_put_contents("/etc/phpmyadmin/config.autoconfig.inc.php",$data , LOCK_EX);
    }
}


// Main event loop
Amp\Loop::run(function () use($containers) {
    
    $instance_name = isset($_ENV['PHPMYADMIN_AUTOCONFIG_INSTANCE']) ? $_ENV['PHPMYADMIN_AUTOCONFIG_INSTANCE']: "phpmyadmin";
    
    // Initialize a Logger

    $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
    $logHandler->setFormatter(new ConsoleFormatter());

    $logger = new Logger('docker');
    $logger->pushHandler($logHandler);

    // Create a client

    $socketPool = new StaticSocketPool('unix:///var/run/docker.sock');
    $dockerClient = new Amp\Artax\DefaultClient(null, new HttpSocketPool($socketPool));
    $serializer = new Serializer(
        [
            new EventsGetResponse200Normalizer(),
            new EventsGetResponse200ActorNormalizer()
        ],
        [
            new JsonEncoder(new JsonEncode(), new JsonDecode())
        ]
    );

    $cancel = new CancellationTokenSource();
    $filters = rawurlencode('{"status":{"running":true} , "label": {"phpmyadmin.autoconfig.target":true}}');
    $response = yield $dockerClient->request(
        'http://docker/containers/json?filters=' . $filters,
        [Client::OP_TRANSFER_TIMEOUT => 0],
        $cancel->getToken()
    );
    /** @var Response $response */   
    $body = $response->getBody();
    $results = "";
    for(;;) {
        if(null !== $chunk = yield $body->read()) {
            $results .= $chunk;
        } else {
            break;
        }
    }

    $pattern_cfg = "/^phpmyadmin\.autoconfig\.cfg\.(.*)/";
    foreach(json_decode($results , true) as $result) {
        $config = [];
        $target = null;
        
        foreach($result['Labels'] as $label => $value) {
            // search label if has phpmyadmin.autoconfig
            if(preg_match ( $pattern_cfg ,$label , $matches)) {
                $config[$matches[1]] = $value;
            } else if($label === "phpmyadmin.autoconfig.target") {
                if($value === $instance_name || $value === "*")
                $target = $value;
            }
        }
        
        $id = substr($result['Id'] , 0, 12);
        if(count($config) > 0 && $target !== null) {
            if(!isset($config['host'])) {
                $config['host'] = $id;
            }
            
            if(isset($config['verbose'])) {
                $id = $config['verbose'];
            }
            
            $containers[$id] = [
                "target" => $target,
                "cfg" => $config
            ];
        }
    }
    
   modify_config_file($containers);
    
    /** @var Response $response */
    $filters = rawurlencode('{"event":{"start":true} , "label": {"phpmyadmin.autoconfig.target":true}}');
    $response = yield $dockerClient->request(
        'http://docker/events?filters=' . $filters,
        [Client::OP_TRANSFER_TIMEOUT => 0],
        $cancel->getToken()
    );

    Amp\call(
        function () use ($response, $serializer, $logger,$containers , $pattern_cfg , $instance_name) {
            $body = $response->getBody();
            $logger->info('Listening to Docker events');
            while (null !== $chunk = yield $body->read()) {
                /** @var EventsGetResponse200 $event */
                $event = $serializer->deserialize($chunk, EventsGetResponse200::class, 'json');
                
                $config = [];
                $target = null;
                foreach($event->getActor()->getAttributes() as $label => $value) {
                        if(preg_match ( $pattern_cfg ,$label , $matches)) {
                            $config[$matches[1]] = $value;
                        } else if($label === "phpmyadmin.autoconfig.target") {
                            if($value === $instance_name || $value === "*")
                            $target = $value;
                        }
                }
                
                $id = substr($event->getActor()->getID() , 0, 12);
                if(count($config) > 0 && $target !== null) {
                    
                    if(!isset($config['host'])) {
                        $config['host'] = $id;
                    }
                    if(isset($config['verbose'])) {
                        $id = $config['verbose'];
                    }

                    $containers[$id] = [
                        "target" => $target,
                        "cfg" => $config
                    ];
                }

                modify_config_file($containers);
            }
        }
    );

    Amp\Loop::onSignal(SIGINT, function (string $watcherId) use ($cancel, $logger) {
        $logger->info('Received SIGINT');
        $cancel->cancel();
        Amp\Loop::cancel($watcherId);
    });

    $logger->info('Loop started');
});