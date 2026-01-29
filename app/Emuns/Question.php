<?php

namespace App\Enums;

enum Question: string {
    case TRUE_FALSE = 'truefalse';
    case MULTICHOICE = 'multichoice';
    case ESSAY = 'essay';
}