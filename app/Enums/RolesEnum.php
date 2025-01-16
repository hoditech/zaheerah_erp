<?php

namespace App\Enums;

enum RolesEnum: string
{
    case TECHNICAL_MANAGER = 'Technical Manager';
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN = 'Admin';
    case HR = 'HR';
    case MANAGER = 'Manager';
    case EMPLOYEE = 'Employee';

    /**
     * Return a human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            static::TECHNICAL_MANAGER => 'Technical Manager',
            static::SUPER_ADMIN => 'Super Administrator',
            static::ADMIN => 'Administrator',
            static::HR => 'Human Resources',
            static::MANAGER => 'Manager',
            static::EMPLOYEE => 'Employee',
        };
    }
}