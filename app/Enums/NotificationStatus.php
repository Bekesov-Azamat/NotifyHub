<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case Pending = 'pending';
    case Processing = 'processsing';
    case Sent = 'sent';
    case Failed = 'failed';
}
