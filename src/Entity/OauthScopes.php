<?php

namespace OAuth2Server\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OauthScopes
 *
 * @ORM\Table(name="oauth_scopes")
 * @ORM\Entity
 */
class OauthScopes
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
     * @var string
     *
     * @ORM\Column(name="scope", type="string", length=2000, nullable=false)
     */
    private $scope;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=true)
     */
    private $is_default;

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
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope(string $scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return bool
     */
    public function getIsDefault(): bool
    {
        return $this->is_default;
    }

    /**
     * @param bool $isDefault
     */
    public function setIsDefault(bool $isDefault)
    {
        $this->is_default = $isDefault;
    }
}
