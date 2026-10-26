<?php

namespace App\Policies;

class LeadPolicy extends CmsPolicy
{
    protected string $area = 'leads';

    protected bool $allowsCreation = false;
}
