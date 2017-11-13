<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OauthJwt
 *
 * @ORM\Table(name="oauth_jwt")
 * @ORM\Entity
 */
class OauthJwt
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=80, nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="public_key", type="string", length=2000, nullable=false)
     */
    private $public_key;

    /**
     * @var \Oauth2Server\Entity\OauthClients
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Oauth2Server\Entity\OauthClients")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="client_id")
     * })
     */
    private $client;

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->public_key;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey)
    {
        $this->public_key = $publicKey;
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
}
