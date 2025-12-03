<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    protected $additionalData = [];

    public function __construct($resource, array $additionalData = [])
    {
        parent::__construct($resource);
        $this->additionalData = $additionalData;
    }

    public function toArray(Request $request): array
    {
        return [
            'notifications' => $this->collection,
            'meta' => [
                'unread_count' => $this->additionalData['unread_count'] ?? 0,
                'total_count' => $this->additionalData['total_count'] ?? 0,
                'read_count' => ($this->additionalData['total_count'] ?? 0) - ($this->additionalData['unread_count'] ?? 0),
            ],
            'pagination' => [
                'current_page' => $this->additionalData['current_page'] ?? 1,
                'last_page' => $this->additionalData['last_page'] ?? 1,
                'per_page' => $this->additionalData['per_page'] ?? 15,
                'total' => $this->additionalData['total'] ?? 0,
                'from' => $this->additionalData['from'] ?? null,
                'to' => $this->additionalData['to'] ?? null,
            ],
        ];
    }
}
