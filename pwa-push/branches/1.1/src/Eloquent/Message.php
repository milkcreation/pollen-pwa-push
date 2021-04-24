<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Eloquent;

use Carbon\Carbon;
use Pollen\Database\Drivers\Laravel\Eloquent\AbstractModel;
use Pollen\Database\Drivers\Laravel\Eloquent\Casts\SerializeCast;

/**
 * @property-read int $id
 * @property array $payload
 * @property Carbon $created_at
 * @property Carbon $send_at
 */
class Message extends AbstractModel
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = 'pwa_push_message';

        $this->casts = [
            'payload' => SerializeCast::class,
            'sent_at' => 'datetime',
        ];

        $this->fillable = [
            'payload',
            'sent_at',
        ];

        parent::__construct($attributes);
    }

    public function getUpdatedAtColumn() { }

    public function setUpdatedAtAttribute($value): void { }
}
