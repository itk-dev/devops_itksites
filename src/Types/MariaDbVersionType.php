<?php

namespace App\Types;

class MariaDbVersionType
{
    public const CHOICES = [
      '5.5' => '5.5',
      '5.7' => '5.7',
      '10.3' => '10.3',
      '10.5' => '10.5',
      '10.6' => '10.6',
    ];
}
