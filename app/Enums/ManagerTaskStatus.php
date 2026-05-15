<?php
namespace App\Enums;

enum ManagerTaskStatus:string {
    case NEW = 'NEW';
    case PENDING = 'PENDING';
    case DELAYED = 'DELAYED';
    case DONE = 'DONE';
    case FAIL = 'FAIL';
}
