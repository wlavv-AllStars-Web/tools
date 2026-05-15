<?php
namespace App\Enums;

enum UserTaskStatus:string {
    case NEW = 'NEW';
    case PENDING = 'PENDING';
    case DONE = 'DONE';
}
