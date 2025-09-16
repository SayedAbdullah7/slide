<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestmentCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class InvestmentCategory extends Model
{
    //
}
