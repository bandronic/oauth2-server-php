<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/10/2017
 * Time: 1:31 PM
 */

namespace OAuth2Server\Service;


use Doctrine\ORM\EntityManager;
use OAuth2\Storage\ClientInterface;
use OAuth2Server\Entity\OauthClients;

class ClientService implements ClientInterface
{
    /** @var  EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get client details corresponding client_id.
     *
     * OAuth says we should store request URIs for each registered client.
     * Implement this function to grab the stored URI for a given client id.
     *
     * @param int $client_id
     * Client identifier to be check with.
     *
     * @return array
     *               Client details. The only mandatory key in the array is "redirect_uri".
     *               This function MUST return FALSE if the given client does not exist or is
     *               invalid. "redirect_uri" can be space-delimited to allow for multiple valid uris.
     *               <code>
     *               return array(
     *               "redirect_uri" => REDIRECT_URI,      // REQUIRED redirect_uri registered for the client
     *               "client_id"    => CLIENT_ID,         // OPTIONAL the client id
     *               "grant_types"  => GRANT_TYPES,       // OPTIONAL an array of restricted grant types
     *               "user_id"      => USER_ID,           // OPTIONAL the user identifier associated with this client
     *               "scope"        => SCOPE,             // OPTIONAL the scopes allowed for this client
     *               );
     *               </code>
     *
     * @ingroup oauth2_section_4
     */
    public function getClientDetails($client_id)
    {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        if ($client == null) {
            return null;
        }

        return $client->jsonSerialize();
    }

    /**
     * Get the scope associated with this client
     *
     * @access public
     * @param int $client_id
     * Client identifier to be check with.
     * @return string
     * STRING the space-delineated scope list for the specified client_id
     */
    public function getClientScope($client_id)
    {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        return $client->getScope();
    }

    /**
     * Check restricted grant types of corresponding client identifier.
     *
     * If you want to restrict clients to certain grant types, override this
     * function.
     *
     * @param int $client_id
     * Client identifier to be check with.
     * @param string $grant_type
     * Grant type to be check with
     *
     * @return bool
     * TRUE if the grant type is supported by this client identifier, and
     * FALSE if it isn't.
     *
     * @ingroup oauth2_section_4
     */
    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        $grants = explode(" ", $client->getGrantTypes());

        $ok = false;
        foreach ($grants as $grant) {
            if ($grant == $grant_type) {
                $ok = true;
                break;
            }
        }

        return $ok;
    }
}