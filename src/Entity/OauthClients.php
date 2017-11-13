<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OauthClients
 *
 * @ORM\Table(name="oauth_clients", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class OauthClients implements \JsonSerializable
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="client_id", type="string", length=80, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $client_id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_secret", type="string", length=80, nullable=true)
     */
    private $client_secret;

    /**
     * @var string
     *
     * @ORM\Column(name="redirect_uri", type="string", length=2000, nullable=true)
     */
    private $redirect_uri;

    /**
     * @var string
     *
     * @ORM\Column(name="grant_types", type="string", length=80, nullable=true)
     */
    private $grant_types;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=2000, nullable=true)
     */
    private $scope;

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
     * @param string $clientId
     */
    public function setClientId(string $clientId)
    {
        $this->client_id = $clientId;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret(string $clientSecret)
    {
        $this->client_secret = $clientSecret;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri(string $redirectUri)
    {
        $this->redirect_uri = $redirectUri;
    }

    /**
     * @param string $grantTypes
     */
    public function setGrantTypes(string $grantTypes)
    {
        $this->grant_types = $grantTypes;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getClientSecret(): ?string
    {
        return $this->client_secret;
    }

    /**
     * @return string
     */
    public function getGrantTypes(): ?string
    {
        return $this->grant_types;
    }

    /**
     * @return string
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getClientId(): ?string
    {
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): ?string
    {
        return $this->redirect_uri;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
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
            "redirect_uri" => $this->getRedirectUri(),      // REQUIRED redirect_uri registered for the client
            "client_id"    => $this->getClientId(),         // OPTIONAL the client id
            "grant_types"  => $this->getGrantTypes(),       // OPTIONAL an array of restricted grant types
            "user_id"      => $this->getUser(),           // OPTIONAL the user identifier associated with this client
            "scope"        => $this->getScope(),
        ];
    }
}
