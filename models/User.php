<?php

class User {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($name, $email, $password) {

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users
            (name, email, password_hash, role)
            VALUES (?, ?, ?, 'customer')
        ");

        return $stmt->execute([
            $name,
            $email,
            $hash
        ]);
    }

    public function findByEmail($email) {

        $stmt = $this->pdo->prepare("
            SELECT * FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);

        return $stmt->fetch();
    }
}
?>