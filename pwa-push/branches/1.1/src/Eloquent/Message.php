<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Eloquent;

use Carbon\Carbon;
use Pollen\Database\Eloquent\AbstractModel;
use Pollen\Database\Eloquent\Casts\SerializedCast;

/**
 * @property-read int $id
 * @property array $payload
 * @property array $context
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $author_id
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
            'payload'   => SerializedCast::class,
            'context'   => SerializedCast::class,
            'author_id' => 'int',
            'send_at'   => 'datetime',
        ];

        $this->fillable = [
            'payload',
            'context',
            'author_id',
            'send_at',
        ];

        parent::__construct($attributes);
    }
}
