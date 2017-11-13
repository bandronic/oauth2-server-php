<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OauthPublicKeys
 *
 * @ORM\Table(name="oauth_public_keys", indexes={@ORM\Index(name="client_id", columns={"client_id"})})
 * @ORM\Entity
 */
class OauthPublicKeys
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
     * @ORM\Column(name="public_key", type="string", length=2000, nullable=true)
     */
    private $public_key;

    /**
     * @var string
     *
     * @ORM\Column(name="private_key", type="string", length=2000, nullable=true)
     */
    private $private_key;

    /**
     * @var string
     *
     * @ORM\Column(name="encryption_algorithm", type="string", length=100, nullable=false)
     */
    private $encryption_algorithm = 'RS256';

    /**
     * @var \Oauth2Server\Entity\OauthClients
     *
     * @ORM\ManyToOne(targetEntity="Oauth2Server\Entity\OauthClients")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="client_id")
     * })
     */
    private $client;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPublicKey(): ?string
    {
        return $this->public_key;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(?string $publicKey)
    {
        $this->public_key = $publicKey;
    }

    /**
     * @return string
     */
    public function getPrivateKey(): ?string
    {
        return $this->private_key;
    }

    /**
     * @param string $privateKey
     */
    public function setPrivateKey(?string $privateKey)
    {
        $this->private_key = $privateKey;
    }

    /**
     * @return string
     */
    public function getEncryptionAlgorithm(): string
    {
        return $this->encryption_algorithm;
    }

    /**
     * @param string $encryptionAlgorithm
     */
    public function setEncryptionAlgorithm(string $encryptionAlgorithm)
    {
        $this->encryption_algorithm = $encryptionAlgorithm;
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
