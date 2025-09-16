<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $investment_opportunity_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereInvestmentOpportunityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Guarantee whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Guarantee extends Model
{
    //
}
