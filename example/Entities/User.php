<?php

namespace Entities;

/**
 * @Entity
 */
class User
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(type="string", length=255, unique=true)
     */
    private $username;

    public function setUsername($username)
    {
        $this->username = $username;
    }
}