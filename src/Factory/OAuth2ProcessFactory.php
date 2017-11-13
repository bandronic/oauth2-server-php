<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/9/2017
 * Time: 12:25 PM
 */

namespace OAuth2Server\Factory;

use Interop\Container\ContainerInterface;
use OAuth2Server\HttpFoundationBridge\Response;
use OAuth2\Server;

class OAuth2ProcessFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $server = $container->get(Server::class);
        $response = $container->get(Response::class);

        return new $requestedName($server, $response);
    }
}