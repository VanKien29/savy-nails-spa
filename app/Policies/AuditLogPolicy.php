<?php

namespace App\Policies;

class AuditLogPolicy extends CmsPolicy
{
    protected string $area = 'audit';

    protected bool $allowsCreation = false;
}
