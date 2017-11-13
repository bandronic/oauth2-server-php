<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/10/2017
 * Time: 1:31 PM
 */

namespace OAuth2Server\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use OAuth2Server\Service\ClientService;

class ClientServiceFactory
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ClientService($container->get(EntityManager::class));
    }
}