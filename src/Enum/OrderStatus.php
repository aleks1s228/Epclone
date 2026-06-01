<?php

namespace App\Enum;

enum OrderStatus: string
{
    case New = 'new';
    case Paid = 'paid';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Canceled = 'canceled';
}