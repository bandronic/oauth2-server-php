<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * OauthJti
 *
 * @ORM\Table(name="oauth_jti")
 * @ORM\Entity
 */
class OauthJti implements JsonSerializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Oauth2Server\Entity\OauthClients
     *
     * @ORM\ManyToOne(targetEntity="Oauth2Server\Entity\OauthClients")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="issuer", referencedColumnName="client_id")
     * })
     */
    private $issuer;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=80, nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="audience", type="string", length=80, nullable=true)
     */
    private $audience;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     */
    private $expires = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="jti", type="string", length=2000, nullable=false)
     */
    private $jti;

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
     * @return OauthClients
     */
    public function getIssuer(): OauthClients
    {
        return $this->issuer;
    }

    /**
     * @param OauthClients $issuer
     */
    public function setIssuer(OauthClients $issuer)
    {
        $this->issuer = $issuer;
    }

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
    public function getAudience(): string
    {
        return $this->audience;
    }

    /**
     * @param string $audience
     */
    public function setAudience(string $audience)
    {
        $this->audience = $audience;
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
     * @return string
     */
    public function getJti(): string
    {
        return $this->jti;
    }

    /**
     * @param string $jti
     */
    public function setJti(string $jti)
    {
        $this->jti = $jti;
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
            'id' => $this->getId(),
            'issuer' => $this->getIssuer(),
            'subject' => $this->getSubject(),
            'audience' => $this->getAudience(),
            'expires' => $this->getExpires(),
            'jti' => $this->getJti(),
        ];
    }
}
