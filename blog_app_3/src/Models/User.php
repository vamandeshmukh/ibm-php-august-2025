<?php
namespace App\Models;

class User
{
    private $id;
    private $username;
    private $email;
    private $password_hash;
    private $created_at;
    private $profile_picture;
    private $bio;

    public function __construct($id = null, $username = null, $email = null, $password_hash = null, $created_at = null, $profile_picture = null, $bio = null)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->created_at = $created_at;
        $this->profile_picture = $profile_picture;
        $this->bio = $bio;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function getProfilePicture()
    {
        return $this->profile_picture;
    }

    public function getBio()
    {
        return $this->bio;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setPasswordHash($password_hash)
    {
        $this->password_hash = $password_hash;
    }

    public function setProfilePicture($profile_picture)
    {
        $this->profile_picture = $profile_picture;
    }

    public function setBio($bio)
    {
        $this->bio = $bio;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'profile_picture' => $this->profile_picture,
            'bio' => $this->bio
        ];
    }
}
?>
