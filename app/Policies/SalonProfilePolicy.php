<?php

namespace App\Policies;

class SalonProfilePolicy extends CmsPolicy
{
    protected string $area = 'profile';

    protected bool $allowsCreation = false;
}
