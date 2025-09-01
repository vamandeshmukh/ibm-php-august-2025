<?php
namespace App\Services;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Models/User.php';

use App\Config\Database;
use App\Models\User;
use PDO;
use PDOException;

class AuthService
{
    private $db;
    private $connection;

    public function __construct()
    {
        $this->db = new Database();
        $this->connection = $this->db->getConnection();
    }

    public function register($username, $email, $password)
    {
        try {
            if ($this->findUserByEmail($email)) {
                print ("$email");
                return ['success' => false, 'message' => 'Email already registered'];
            }

            if ($this->findUserByUsername($username)) {
                return ['success' => false, 'message' => 'Username already taken'];
            }

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (username, email, password_hash, created_at) 
                      VALUES (:username, :email, :password_hash, NOW())";

            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $passwordHash);

            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Registration successful'];
            }

            return ['success' => false, 'message' => 'Registration failed'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function login($email, $password)
    {
        try {
            $user = $this->findUserByEmail($email);

            if (!$user) {
                return ['success' => false, 'message' => 'Invalid email or password'];
            }

            if (password_verify($password, $user->getPasswordHash())) {
                $this->startUserSession($user);
                return ['success' => true, 'message' => 'Login successful'];
            }

            return ['success' => false, 'message' => 'Invalid email or password'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function logout()
    {
        $_SESSION = array();

        if (session_id() != "" || isset($_COOKIE[session_name()])) {
            session_destroy();
        }

        setcookie(session_name(), '', time() - 3600, '/');
    }
    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        try {
            $query = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return new User(
                    $row['id'],
                    $row['username'],
                    $row['email'],
                    $row['password_hash'],
                    $row['created_at'],
                    $row['profile_picture'],
                    $row['bio']
                );
            }

            return null;

        } catch (PDOException $e) {
            return null;
        }
    }

    private function findUserByEmail($email)
    {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return new User(
                    $row['id'],
                    $row['username'],
                    $row['email'],
                    $row['password_hash'],
                    $row['created_at'],
                    $row['profile_picture'],
                    $row['bio']
                );
            }

            return null;

        } catch (PDOException $e) {
            return null;
        }
    }

    private function findUserByUsername($username)
    {
        try {
            $query = "SELECT * FROM users WHERE username = :username";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return new User(
                    $row['id'],
                    $row['username'],
                    $row['email'],
                    $row['password_hash'],
                    $row['created_at'],
                    $row['profile_picture'],
                    $row['bio']
                );
            }

            return null;

        } catch (PDOException $e) {
            return null;
        }
    }

    private function startUserSession(User $user)
    {
        session_start();
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['email'] = $user->getEmail();
    }

    public function updateProfile($userId, $username, $email, $bio = null, $profilePicture = null)
    {
        try {
            $existingUser = $this->findUserByUsername($username);
            if ($existingUser && $existingUser->getId() != $userId) {
                return ['success' => false, 'message' => 'Username already taken'];
            }

            $existingUser = $this->findUserByEmail($email);
            if ($existingUser && $existingUser->getId() != $userId) {
                return ['success' => false, 'message' => 'Email already registered'];
            }

            $query = "UPDATE users SET username = :username, email = :email, bio = :bio";
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':bio' => $bio
            ];

            if ($profilePicture) {
                $query .= ", profile_picture = :profile_picture";
                $params[':profile_picture'] = $profilePicture;
            }

            $query .= " WHERE id = :id";
            $params[':id'] = $userId;

            $stmt = $this->connection->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                return ['success' => true, 'message' => 'Profile updated successfully'];
            }

            return ['success' => false, 'message' => 'Profile update failed'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            $query = "SELECT password_hash FROM users WHERE id = :id";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':id', $userId);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($currentPassword, $row['password_hash'])) {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateQuery = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
                    $updateStmt = $this->connection->prepare($updateQuery);
                    $updateStmt->bindParam(':password_hash', $newPasswordHash);
                    $updateStmt->bindParam(':id', $userId);

                    if ($updateStmt->execute()) {
                        return ['success' => true, 'message' => 'Password changed successfully'];
                    }
                }
            }

            return ['success' => false, 'message' => 'Current password is incorrect'];

        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
