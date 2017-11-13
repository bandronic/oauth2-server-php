<?php
/**
 * Created by PhpStorm.
 * User: Bogdan
 * Date: 11/9/2017
 * Time: 12:31 PM
 */

namespace OAuth2Server\Entity;

/**
 * Interface UserInterface
 * @package OAuth2Server\Entity
 *
 * The implementing user entity MUST be a Doctrine ORM entity
 */
interface UserInterface
{
    public function getId();
    public function getUsername();
    public function getPassword();
    public function setUsername(string $username);
    public function setPassword(string $password);

    public function getIdField();
    public function getUsernameField();
    public function getPasswordField();
}