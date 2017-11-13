<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/9/2017
 * Time: 12:25 PM
 */

namespace OAuth2Server\Factory;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use OAuth2\GrantType\AuthorizationCode;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server as OAuth2Server;
use OAuth2\Storage\Memory;
use OAuth2Server\Storage\Pdo;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $configOptions = $container->get("config")["oauth2"] ?? [];

        if (empty($configOptions)) {
            if (empty($configOptions['user_entity'])) {
                throw new \Exception('Missing user_entity!
             Please provide the user_entity in the config file under the user_entity entry');
            }

            if (empty($configOptions['client_service'])) {
                throw new \Exception('Missing user_entity!
             Please provide the client_entity in the config file under the client_entity entry');
            }
        }

        $options = array_merge([
            'access_lifetime' => 3600,
            'enforce_state' => true,
            'allow_implicit' => true,
            'use_openid_connect' => true,
            'always_issue_new_refresh_token' => true,
            'issuer' => $_SERVER['HTTP_HOST']
        ], $configOptions);

        // create PDO-based sqlite storage
        $storage = new Pdo($container->get(EntityManager::class), $configOptions['user_entity']);

        // create array of supported grant types
        $grantTypes = array(
            'user_credentials' => new UserCredentials($storage),
            'authorization_code' => new AuthorizationCode($storage),
            'refresh_token' => new RefreshToken($storage, [
                'always_issue_new_refresh_token' => $options['always_issue_new_refresh_token'],
            ]),
        );
        // instantiate the oauth server
        $server = new OAuth2Server($storage, $options, $grantTypes);
        $server->addStorage($this->getKeyStorage($options['keys_folder']), 'public_key');
        $server->addStorage($container->get($configOptions['client_service']), 'client');

        return $server;
    }

    private function getKeyStorage($keysFolder)
    {
        $publicKey = file_get_contents($keysFolder . '/public.key');
        $privateKey = file_get_contents($keysFolder . '/private.key');
        // create storage
        $keyStorage = new Memory(array(
            'keys' => array(
                'public_key' => $publicKey,
                'private_key' => $privateKey,
            )
        ));
        return $keyStorage;
    }
}