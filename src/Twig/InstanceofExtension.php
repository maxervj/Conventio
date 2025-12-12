<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceofExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [
            new TwigTest('instanceof', [$this, 'isInstanceof']),
        ];
    }

    public function isInstanceof($object, string $class): bool
    {
        if (!is_object($object)) {
            return false;
        }

        return $object instanceof $class;
    }
}
