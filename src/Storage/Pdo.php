<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/9/2017
 * Time: 12:59 PM
 */

namespace OAuth2Server\Storage;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use OAuth2\Storage\ClientInterface;
use OAuth2Server\ConfigProvider;
use OAuth2Server\Entity\OauthAccessTokens;
use OAuth2Server\Entity\OauthAuthorizationCodes;
use OAuth2Server\Entity\OauthClients;
use OAuth2Server\Entity\OauthJti;
use OAuth2Server\Entity\OauthJwt;
use OAuth2Server\Entity\OauthPublicKeys;
use OAuth2Server\Entity\OauthRefreshTokens;
use OAuth2Server\Entity\OauthScopes;
use OAuth2Server\Entity\UserInterface;

class Pdo extends \OAuth2\Storage\Pdo
{
    protected $db;
    /** @var EntityManager */
    protected $em;

    /** @var  string */
    private $userClass;

    public function __construct(EntityManager $em, $user)
    {
        $this->em = $em;
        $this->userClass = $user;
    }

    public function checkClientCredentials($client_id, $client_secret = null): bool
    {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        return $client != null && $client->getClientSecret() == $client_secret;
    }

    public function isPublicClient($client_id): bool
    {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        return $client != null && empty($client->getClientSecret());
    }

    public function getClientDetails($client_id): OauthClients
    {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        return $client;
    }

    public function setClientDetails(
        $client_id,
        $client_secret = null,
        $redirect_uri = null,
        $grant_types = null,
        $scope = null,
        $user_id = null
    ): OauthClients {
        /** @var OauthClients $client */
        $client = $this->em->getRepository(OauthClients::class)->findOneBy(['client_id' => $client_id]);

        if ($client == null) {
            $client = new OauthClients();
            $client->setClientId($client_id);
        }

        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);
        $client->setGrantTypes($grant_types);
        $client->setScope($scope);
        $client->setUser($this->getUser($user_id));

        $this->em->persist($client);
        $this->em->flush();

        return $client;
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        /** @var OauthClients $details */
        $details = $this->getClientDetails($client_id);
        if ($details->getGrantTypes() != '') {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array)$grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    public function getAccessToken($access_token): ?array
    {
        /** @var OauthAccessTokens $token */
        $token = $this->em->getRepository(OauthAccessTokens::class)->findOneBy(['access_token' => $access_token]);

        if ($token == null) {
            return null;
        }

        return $token->jsonSerialize();
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null): OauthAccessTokens
    {
        // convert expires to datestring
        $expires = new \DateTime(date('Y-m-d H:i:s', $expires));

        /** @var OauthAccessTokens $token */
        $token = $this->em->getRepository(OauthAccessTokens::class)->findOneBy(['access_token' => $access_token]);
        if ($token == null) {
            $token = new OauthAccessTokens();
            $token->setAccessToken($access_token);
        }

        $token->setClient($this->getClientDetails($client_id));
        $token->setUser($this->getUser($user_id));
        $token->setExpires($expires);
        $token->setScope($scope);

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    public function unsetAccessToken($access_token): bool
    {
        $token = $this->em->getRepository(OauthAccessTokens::class)->findOneBy(['access_token' => $access_token]);
        if ($token != null) {
            $this->em->remove($token);
            $this->em->flush();

            return true;
        }

        return false;
    }

    public function getAuthorizationCode($code): ?array
    {
        /** @var OauthAuthorizationCodes $authCode */
        $authCode = $this->em->getRepository(OauthAuthorizationCodes::class)->findOneBy(['authorization_code' => $code]);

        if ($authCode == null) {
            return null;
        }

        return $authCode->jsonSerialize();
    }

    public function setAuthorizationCode(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null
    ): OauthAuthorizationCodes {
        $expires = new \DateTime(date('Y-m-d H:i:s', $expires));

        $codeEntity = $this->getAuthorizationCode($code);

        if ($codeEntity == null) {
            $codeEntity = new OauthAuthorizationCodes();
            $codeEntity->setAuthorizationCode($code);
        }

        $codeEntity->setClient($this->getClientDetails($client_id));
        $codeEntity->setUser($this->getUser($user_id));
        $codeEntity->setRedirectUri($redirect_uri);
        $codeEntity->setExpires($expires);
        $codeEntity->setScope($scope);
        $codeEntity->setIdToken($id_token);

        $this->em->persist($codeEntity);
        $this->em->flush();

        return $codeEntity;
    }

    private function setAuthorizationCodeWithIdToken(
        $code,
        $client_id,
        $user_id,
        $redirect_uri,
        $expires,
        $scope = null,
        $id_token = null
    ) {
        $expires = new \DateTime(date('Y-m-d H:i:s', $expires));

        $codeEntity = $this->getAuthorizationCode($code);

        if ($codeEntity == null) {
            $codeEntity = new OauthAuthorizationCodes();
            $codeEntity->setAuthorizationCode($code);
        }

        $codeEntity->setClient($this->getClientDetails($client_id));
        $codeEntity->setUser($this->getUser($user_id));
        $codeEntity->setRedirectUri($redirect_uri);
        $codeEntity->setExpires($expires);
        $codeEntity->setScope($scope);
        $codeEntity->setIdToken($id_token);

        $this->em->persist($codeEntity);
        $this->em->flush();

        return $codeEntity;
    }

    public function expireAuthorizationCode($code)
    {
        $code = $this->em->getRepository(OauthAuthorizationCodes::class)->findOneBy(['authorization_code' => $code]);

        if ($code != null) {
            $this->em->remove($code);
            $this->em->flush();

            return true;
        }

        return false;
    }

    public function checkUserCredentials($username, $password)
    {
        if ($user = $this->getUserDetails($username)) {
            return $this->checkPassword($user, $password);
        }

        return false;
    }

    public function getUserDetails($username): ?array
    {
        /** @var UserInterface $user */
        $user = $this->em->getRepository($this->userClass)->findOneBy([(new $this->userClass())->getUsernameField() => $username]);

        if ($user != null) {
            return [
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
                'user_id' => $user->getId(),
            ];
        }

        return null;
    }

    public function getUserClaims($user_id, $claims): array
    {
        if (!$userDetails = $this->getUser($user_id)) {
            return false;
        }

        $claims = explode(' ', trim($claims));
        $userClaims = array();

        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
            }
        }

        return $userClaims;
    }

    protected function getUserClaim($claim, $userDetails)
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);

        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        }

        return $userClaims;
    }

    public function getRefreshToken($refresh_token): ?array
    {
        $refreshToken = $this->em->getRepository(OauthRefreshTokens::class)->findOneBy(['refresh_token' => $refresh_token]);

        if ($refreshToken == null) {
            return null;
        }

        return $refreshToken->jsonSerialize();
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null): ?OauthRefreshTokens
    {
        // convert expires to datestring
        $expires = new \DateTime(date('Y-m-d H:i:s', $expires));

        $refreshToken = new OauthRefreshTokens();
        $refreshToken->setRefreshToken($refresh_token);
        $refreshToken->setClient($this->getClientDetails($client_id));
        $refreshToken->setUser($this->getUser($user_id));
        $refreshToken->setExpires($expires);
        $refreshToken->setScope($scope);

        $this->em->persist($refreshToken);
        $this->em->flush();

        return $refreshToken;
    }

    public function unsetRefreshToken($refresh_token): bool
    {
        $refreshToken = $this->em->getRepository(OauthRefreshTokens::class)->findOneBy(['refresh_token' => $refresh_token]);
        if ($refreshToken != null) {
            $this->em->remove($refreshToken);
            $this->em->flush();

            return true;
        }

        return false;
    }

    protected function checkPassword($user, $password)
    {
        return password_verify($password, $user['password']);
    }

    protected function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getUser($user_id): ?UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->em->getRepository($this->userClass)->findOneBy(['id' => $user_id]);

        return $user;
    }

    public function setUser($username, $password, $firstName = null, $lastName = null): ?UserInterface
    {
        // do not store in plaintext
        $password = $this->hashPassword($password);

        $user = $this->getUser($username);

        if ($user == null) {
            /** @var UserInterface $user */
            $user = new $this->userClass();
        }

        $user->setUsername($username);
        $user->setPassword($password);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function scopeExists($scope): bool
    {
        $scope = explode(' ', $scope);

//        $qb = $this->em->createQueryBuilder();
//        $qb->add('SELECT')
//            ->add('FROM', 'oauth_scopes')
//            ->add('WHERE', $qb->expr()->in('scope', array('?1')));
//        $qb->setParameter(1, $scope);
//        $query = $qb->getQuery();
//        $scopes = $query->getResult();

        $scopes = $this->em->getRepository(OauthScopes::class)->findBy(['scope' => $scope]);

        if ($scopes == null) {
            return false;
        }

        return count($scopes) == count($scope);
    }

    public function getDefaultScope($client_id = null): ?string
    {
        /** @var OauthScopes[] $result */
        $result = $this->em->getRepository(OauthScopes::class)->findBy(['is_default' => true]);

        if ($result != null) {
            $defaultScope = array_map(function (OauthScopes $row) {
                return $row->getScope();
            }, $result);

            return implode(' ', $defaultScope);
        }

        return null;
    }

    public function getClientKey($client_id, $subject): ?string
    {
        /** @var OauthJwt $clientKey */
        $clientKey = $this->em->getRepository(OauthJwt::class)->findOneBy([
            'client_id' => $client_id,
            'subject' => $subject
        ]);

        if ($clientKey == null) {
            return null;
        }

        return $clientKey->getPublicKey();
    }

    public function getClientScope($client_id): string
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return null;
        }

        return $clientDetails->getScope();
    }

    public function getJti($client_id, $subject, $audience, $expires, $jti): ?OauthJti
    {
        $jti = $this->em->getRepository(OauthJti::class)->findBy([
            'issuer' => $client_id,
            'subject' => $subject,
            'audience' => $audience,
            'expires' => $expires,
            'jti' => $jti
        ]);

        return $jti;
    }

    public function setJti($client_id, $subject, $audience, $expires, $jti): OauthJti
    {
        $jti = new OauthJti();

        $jti->setIssuer($this->getClientDetails($client_id));
        $jti->setSubject($subject);
        $jti->setAudience($audience);
        $jti->setExpires($expires);
        $jti->setJti($jti);

        $this->em->persist($jti);
        $this->em->flush();

        return $jti;
    }

    private function getPublicKeyEntity($client_id = null): ?OauthPublicKeys
    {
        $criteria = new Criteria();
        $criteria
            ->orWhere($criteria->expr()->contains('client_id', $client_id))
            ->orWhere($criteria->expr()->neq('client_id', null));

        $publicKeys = $this->em->getRepository(OauthPublicKeys::class)->matching($criteria);

        if ($publicKeys->isEmpty()) {
            return null;
        }

        /** @var OauthPublicKeys $publicKey */
        $publicKey = $publicKeys->first();

        return $publicKey;
    }

    public function getPublicKey($client_id = null): ?string
    {
        $publicKey = $this->getPublicKeyEntity($client_id);

        if ($publicKey == null) {
            return null;
        }

        return $publicKey->getPublicKey();
    }

    public function getPrivateKey($client_id = null): ?string
    {
        $publicKey = $this->getPublicKeyEntity($client_id);

        if ($publicKey == null) {
            return null;
        }

        return $publicKey->getPrivateKey();
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        $publicKey = $this->getPublicKeyEntity($client_id);

        if ($publicKey == null) {
            return 'RS256';
        }

        return $publicKey->getEncryptionAlgorithm();
    }
}