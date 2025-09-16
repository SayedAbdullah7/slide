<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $opportunity_id
 * @property string $type
 * @property string $file_path
 * @property int|null $file_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereOpportunityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpportunityAttachment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OpportunityAttachment extends Model
{
    //
}
