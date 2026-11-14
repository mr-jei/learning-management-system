<?php

abstract class User {
    protected string $userId;
    protected string $name;
    protected string $email;

    public function __construct(string $userId, string $name, string $email) {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
    }

    public function login(): string {
        return "{$this->name} logged in successfully.";
    }

    public function logout(): string {
        return "{$this->name} logged out.";
    }

    public function getUserId(): string { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
}
