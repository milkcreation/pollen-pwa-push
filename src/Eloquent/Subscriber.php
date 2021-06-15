<?php

declare(strict_types=1);

namespace Pollen\PwaPush\Eloquent;

use Carbon\Carbon;
use Pollen\Database\Eloquent\AbstractModel;

/**
 * @property-read int $id
 * @property string $auth_token
 * @property string $content_encoding
 * @property string $endpoint
 * @property string $public_key
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $client_ip
 * @property string $user_agent
 * @property int $user_id
 * @property-read array $subscription
 */
class Subscriber extends AbstractModel
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = 'pwa_push_subscriber';

        $this->casts = [
            'auth_token'       => 'string',
            'content_encoding' => 'string',
            'endpoint'         => 'string',
            'public_key'       => 'string',
            'client_ip'        => 'string',
            'user_agent'       => 'string',
            'user_id'          => 'integer',
        ];

        $this->fillable = [
            'auth_token',
            'content_encoding',
            'endpoint',
            'public_key',
            'client_ip',
            'user_agent',
            'user_id',
        ];

        parent::__construct($attributes);
    }

    /**
     * Récupération des informations d'abonnement.
     *
     * @return array
     */
    public function getSubscriptionAttribute(): array
    {
        return [
            'authToken'       => $this->auth_token,
            'contentEncoding' => $this->content_encoding,
            'endpoint'        => $this->endpoint,
            'publicKey'       => $this->public_key,
        ];
    }
}
