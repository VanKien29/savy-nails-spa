<?php

namespace App\Policies;

class SiteSettingPolicy extends CmsPolicy
{
    protected string $area = 'settings';

    protected bool $allowsCreation = false;
}
