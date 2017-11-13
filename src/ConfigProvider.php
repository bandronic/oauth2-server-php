<?php

namespace OAuth2Server;

use OAuth2Server\Entity\OauthClients;
use OAuth2Server\Factory\ClientServiceFactory;
use OAuth2Server\HttpFoundationBridge\Response;
use OAuth2\Server;
use OAuth2Server\Factory\OAuth2ProcessFactory;
use OAuth2Server\Factory\ServerFactory;
use OAuth2Server\Middleware\Authorize;
use OAuth2Server\Middleware\Token;
use OAuth2Server\Middleware\VerifyResource;
use OAuth2Server\Service\ClientService;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * The configuration provider for the OAuth2 module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'oauth2' => [
                'client_service'       => ClientService::class,
            ],
            'doctrine' => [
                'driver' => [
                    'orm_default' => [
                        'class' => \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain::class,
                        'drivers' => [
                            'OAuth2Server\Entity' => 'oauth2_entity',
                        ],
                    ],
                    'oauth2_entity' => [
                        'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                        'cache' => 'array',
                        'paths' => __DIR__ . '/Entity',
                    ],
                ],
            ]
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                Server::class => ServerFactory::class,
                Response::class => InvokableFactory::class,
                Authorize::class => OAuth2ProcessFactory::class,
                Token::class => OAuth2ProcessFactory::class,
                VerifyResource::class => OAuth2ProcessFactory::class,
                OauthClients::class => InvokableFactory::class,
                ClientService::class => ClientServiceFactory::class,
            ],
        ];
    }

}
