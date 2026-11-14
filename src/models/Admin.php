<?php
require_once __DIR__ . '/User.php';

class Admin extends User {
    public function manageUsers(): string {
        return "Admin {$this->name} accessed system user management.";
    }
}
