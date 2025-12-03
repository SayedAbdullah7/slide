<?php

namespace App\Support;

use App\Models\User;
use App\Models\InvestorProfile;
use App\Models\OwnerProfile;

class CurrentProfile
{
    public function __construct(
        public ?string $type = null, // 'investor'|'owner'|null
        public InvestorProfile|OwnerProfile|null $model = null,
        public ?User $user = null,
    ) {}
}
