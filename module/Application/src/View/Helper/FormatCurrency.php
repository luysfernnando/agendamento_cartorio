<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class FormatCurrency extends AbstractHelper
{
    public function __invoke(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
} 