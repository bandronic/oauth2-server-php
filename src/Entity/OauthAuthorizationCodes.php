<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OauthAuthorizationCodes
 *
 * @ORM\Table(name="oauth_authorization_codes", indexes={@ORM\Index(name="user_id", columns={"user_id"}), @ORM\Index(name="client_id", columns={"client_id"})})
 * @ORM\Entity
 */
class OauthAuthorizationCodes implements \JsonSerializable
{
    /**
     * @var string
     *
     * @ORM\Column(name="authorization_code", type="string", length=40, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $authorization_code;

    /**
     * @var string
     *
     * @ORM\Column(name="redirect_uri", type="string", length=2000, nullable=true)
     */
    private $redirect_uri;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     */
    private $expires = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=2000, nullable=true)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="id_token", type="string", length=2000, nullable=true)
     */
    private $id_token;

    /**
     * @var \OAuth2Server\Entity\UserInterface
     *
     * @ORM\ManyToOne(targetEntity="OAuth2Server\Entity\UserInterface")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \OAuth2Server\Entity\OauthClients
     *
     * @ORM\ManyToOne(targetEntity="OAuth2Server\Entity\OauthClients")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="client_id")
     * })
     */
    private $client;

    /**
     * @return string
     */
    public function getAuthorizationCode(): ?string
    {
        return $this->authorization_code;
    }

    /**
     * @param string $authorizationCode
     */
    public function setAuthorizationCode(?string $authorizationCode)
    {
        $this->authorization_code = $authorizationCode;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): ?string
    {
        return $this->redirect_uri;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri(?string $redirectUri)
    {
        $this->redirect_uri = $redirectUri;
    }

    /**
     * @return \DateTime
     */
    public function getExpires(): ?\DateTime
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     */
    public function setExpires(?\DateTime $expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(?string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getIdToken(): ?string
    {
        return $this->id_token;
    }

    /**
     * @param string $idToken
     */
    public function setIdToken(?string $idToken)
    {
        $this->id_token = $idToken;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(?UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return OauthClients
     */
    public function getClient(): ?OauthClients
    {
        return $this->client;
    }

    /**
     * @param OauthClients $client
     */
    public function setClient(?OauthClients $client)
    {
        $this->client = $client;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'authorization_code' => $this->getAuthorizationCode(),
            'redirect_uri' => $this->getRedirectUri(),
            'client_id' => $this->getClient()->getClientId(),
            'user_id' => $this->getUser() == null ? '' : $this->getUser()->getId(),
            'scope' => $this->getScope(),
            'expires' => $this->getExpires() == null? '' : $this->getExpires()->getTimestamp(),
            'id_token' => $this->getIdToken(),
        ];
    }
}
