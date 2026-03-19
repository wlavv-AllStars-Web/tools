<?php
namespace App\Enums;

enum AdminTaskStatus:string {
    case DONE = 'DONE';
    case FAIL = 'FAIL';
    case DELAYED = 'DELAYED';
    case PENDING = 'PENDING';
    case OK = 'OK';
    case EXTRA = 'EXTRA';
    case HOLD = 'HOLD';
}
