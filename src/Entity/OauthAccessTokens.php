<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OauthAccessTokens
 *
 * @ORM\Table(name="oauth_access_tokens", indexes={@ORM\Index(name="user_id", columns={"user_id"}), @ORM\Index(name="client_id", columns={"client_id"})})
 * @ORM\Entity
 */
class OauthAccessTokens implements \JsonSerializable
{
    /**
     * @var string
     *
     * @ORM\Column(name="access_token", type="string", length=40, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $access_token;

    /**
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=2000, nullable=true)
     */
    private $scope;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     */
    private $expires = 'CURRENT_TIMESTAMP';

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
    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken)
    {
        $this->access_token = $accessToken;
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
     * @return \DateTime
     */
    public function getExpires(): \DateTime
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     */
    public function setExpires(\DateTime $expires)
    {
        $this->expires = $expires;
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
    public function getClient(): OauthClients
    {
        return $this->client;
    }

    /**
     * @param OauthClients $client
     */
    public function setClient(OauthClients $client)
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
            'access_token' => $this->getAccessToken(),
            'expires' => $this->getExpires()->getTimestamp(),
            'client_id' => $this->getClient()->getClientId(),
            'user_id' => $this->getUser()->getId(),
            'scope' => $this->getScope(),
        ];
    }
}
