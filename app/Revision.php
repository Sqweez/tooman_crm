<?php

namespace App;

use App\Traits\HasFormattedDate;
use App\v2\Models\WriteOff;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Revision
 *
 * @property int $id
 * @property int $user_id
 * @property int $store_id
 * @property int $is_finished
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\RevisionProducts|null $revision_products
 * @property-read \App\Store $store
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Revision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision query()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $finished_at
 * @property-read string|null $finish_date
 * @property-read string $start_date
 * @property-read int|null $revision_products_count
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereFinishedAt($value)
 * @property int|null $checking_user_id
 * @property string|null $original_loaded_revision_file
 * @property string|null $original_pivot_file
 * @property string|null $final_pivot_file
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereCheckingUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereFinalPivotFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereOriginalLoadedRevisionFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereOriginalPivotFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereStatus($value)
 * @property-read \App\User|null $checker
 * @property string|null $revision_sent_to_approve_at
 * @property-read string $date_formatted
 * @property-read string $status_text
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereRevisionSentToApproveAt($value)
 * @property-read bool $can_approve
 * @property-read bool $can_sent_to_approve
 * @property string|null $original_generated_revision_file
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereOriginalGeneratedRevisionFile($value)
 * @property-read \Illuminate\Support\Collection $files
 * @property string|null $edited_pivot_file
 * @property string|null $edited_pivot_at
 * @property-read bool $can_edit
 * @property-read bool $can_finish
 * @property-read bool $can_generate_pivot
 * @property-read bool $is_checker
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereEditedPivotAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereEditedPivotFile($value)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read bool $can_rollback
 * @method static \Illuminate\Database\Query\Builder|Revision onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Revision whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Revision withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Revision withoutTrashed()
 * @property-read WriteOff|null $writeOff
 * @property-read bool $is_posting_disabled
 * @property-read bool $is_write_off_disabled
 * @property-read \App\Posting|null $posting
 */
class Revision extends Model
{

    use HasFormattedDate, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_finished' => 'boolean',
    ];

    const STATUS_STARTED = 1;
    const STATUS_ON_APPROVE = 2;
    const STATUS_FINISHED = 3;
    const STATUS_IN_PROCESS = 4;

    const FILE_TEMPLATE = 'revision_template.xlsx';
    const PIVOT_FILE_TEMPLATE = 'revision_pivot_template.xlsx';
    const RESULT_PIVOT_FILE_TEMPLATE = 'result_revision_pivot_template.xlsx';

    const FILE_NAME = 'РЕВИЗИЯ';
    const PIVOT_FILE_NAME = 'ПРОМЕЖУТОЧНАЯ_СВОДНАЯ_ТАБЛИЦА';
    const RESULT_PIVOT_FILE_NAME = 'СВОДНАЯ_ТАБЛИЦА';

    public function user(): BelongsTo {
        return $this->belongsTo(User::class)
            ->select(['id', 'name']);
    }

    public function checker(): BelongsTo {
        return $this->belongsTo(User::class, 'checking_user_id')
            ->select(['id', 'name'])
            ->withDefault([
                'id' => null,
                'name' => 'Не установлен'
            ]);
    }

    public function store(): BelongsTo {
        return $this->belongsTo(Store::class)
            ->select(['id', 'name']);
    }

    public function revision_products(): HasMany {
        return $this->hasMany(RevisionProducts::class, 'revision_id');
    }

    public function getStartDateAttribute(): string {
        return Carbon::parse($this->created_at)->format('d.m.Y H:i:s');
    }

    public function getFinishDateAttribute(): ?string {
        return $this->finished_at ? Carbon::parse($this->finished_at)->format('d.m.Y H:i:s') : null;
    }

    public function writeOff(): HasOne {
        return $this->hasOne(WriteOff::class)->whereIn('status', [WriteOff::STATUS_ACCEPTED, WriteOff::STATUS_PENDING]);
    }

    public function posting(): HasOne {
        return $this->hasOne(Posting::class)->whereIn('status', [Posting::STATUS_ACCEPTED, Posting::STATUS_PENDING]);
    }

    public function getStatusTextAttribute(): string {
        switch ($this->status) {
            case self::STATUS_STARTED:
                return 'В процессе';
            case self::STATUS_ON_APPROVE:
                return 'На проверке';
            case self::STATUS_FINISHED:
                return 'Закончена';
            case self::STATUS_IN_PROCESS:
                return 'Обрабатывается';
            default:
                return 'Неизвестно';
        }
    }

    public function getCanSentToApproveAttribute(): bool {
        $user = auth()->user();
        return $user && ($user->is_super_user || $user->id === $this->user_id) && $this->status === self::STATUS_STARTED;
    }

    public function getCanApproveAttribute(): bool {
        $user = auth()->user();
        return $user && $user->is_super_user && $this->status === self::STATUS_ON_APPROVE;
    }

    public function getCanGeneratePivotAttribute(): bool {
        return $this->getCanApproveAttribute() && is_null($this->original_pivot_file);
    }

    public function getIsCheckerAttribute(): bool {
        return $this->checking_user_id === auth()->id() && $this->status === self::STATUS_ON_APPROVE;
    }

    public function getCanEditAttribute(): bool {
        return $this->getIsCheckerAttribute() && is_null($this->edited_pivot_at);
    }

    public function getCanFinishAttribute(): bool {
        return $this->getIsCheckerAttribute() && !is_null($this->original_pivot_file);
    }

    public function getCanRollbackAttribute(): bool {
        return $this->getIsCheckerAttribute() && !is_null($this->edited_pivot_file);
    }

    public function getIsWriteOffDisabledAttribute(): bool {
        return $this->writeOff|| $this->revision_products->filter(function ($item) {
                return $item['fact_quantity'] < $item['stock_quantity'];
            })->count() === 0;
    }

    public function getIsPostingDisabledAttribute(): bool {
        return isset($this->posting) || $this->revision_products->filter(function ($item) {
            return $item['fact_quantity'] > $item['stock_quantity'];
        })->count() === 0;
    }

    public function getFilesAttribute(): \Illuminate\Support\Collection {
        return collect([
            [
                'name' => 'Сгенерированный файл ревизии',
                'value' => get_external_file_url($this->original_generated_revision_file, false)
            ],
            [
                'name' => 'Загруженный файл ревизии',
                'value' => get_external_file_url($this->original_loaded_revision_file, false)
            ],
            [
                'name' => 'Оригинальная сводная таблица',
                'value' => get_external_file_url($this->original_pivot_file, false)
            ],
            [
                'name' => 'Отредактированная сводная таблица',
                'value' => get_external_file_url($this->edited_pivot_file, false)
            ],
            [
                'name' => 'Итоговая сводная таблица',
                'value' => get_external_file_url($this->final_pivot_file, false)
            ],
        ])->filter(function ($file) {
            return !is_null($file['value']);
        });
    }
}
