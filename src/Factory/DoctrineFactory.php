<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/10/2017
 * Time: 2:21 PM
 */

namespace OAuth2Server\Factory;

use ContainerInteropDoctrine\EntityManagerFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use OAuth2Server\Entity\UserInterface;
use Interop\Container\ContainerInterface;

class DoctrineFactory extends EntityManagerFactory
{
    /**
     * @param ContainerInterface $container
     * @return EntityManager
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container): EntityManager
    {
        $em = parent::__invoke($container);

        $configOptions = $container->get("config")["oauth2"] ?? [];

        if (empty($configOptions)) {
            if (empty($configOptions['user_entity'])) {
                throw new \Exception('Missing user_entity!
             Please provide the user_entity in the config file under the user_entity entry');
            }
        }

        $eventListener = new ResolveTargetEntityListener();
        $eventListener->addResolveTargetEntity(UserInterface::class, $configOptions['user_entity'], array());

        $em->getEventManager()->addEventListener(Events::loadClassMetadata, $eventListener);

        return $em;
    }
}