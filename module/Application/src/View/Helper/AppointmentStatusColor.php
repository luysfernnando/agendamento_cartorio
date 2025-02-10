<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class AppointmentStatusColor extends AbstractHelper
{
    public function __invoke(string $status): string
    {
        return match ($status) {
            'confirmed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }
} 