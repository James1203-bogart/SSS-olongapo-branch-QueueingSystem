<?php

namespace App\Services;

use Illuminate\Support\Str;

class CouchbaseTicketRepository
{
    private CouchbaseClient $cb;

    public function __construct(CouchbaseClient $cb)
    {
        $this->cb = $cb;
    }

    public function add(array $ticket): array
    {
        $id = $ticket['id'] ?? Str::uuid()->toString();
        $ticket['id'] = $id;
        $ticket['timestamp'] = $ticket['timestamp'] ?? now()->toISOString();
        $this->cb->collection()->upsert($id, $ticket);
        return $ticket;
    }

    public function listByBranch(string $branch): array
    {
        $cfg = config('couchbase');
        $bucket = $cfg['bucket'] ?? '';
        $scope = $cfg['scope'] ?? '_default';
        $collection = $cfg['collection'] ?? 'tickets';
        $stmt = sprintf('SELECT t.* FROM `%s`.`%s`.`%s` t WHERE t.branch = $branch ORDER BY t.timestamp ASC', $bucket, $scope, $collection);
        $res = $this->cb->query($stmt, ['branch' => $branch]);
        $rows = [];
        foreach ($res->rows() as $row) {
            $rows[] = (array) $row;
        }
        return $rows;
    }

    public function updateStatus(string $id, string $status, ?string $counter = null): void
    {
        $doc = $this->cb->collection()->get($id)->content();
        $doc['status'] = $status;
        if ($counter !== null) $doc['counter'] = $counter;
        $this->cb->collection()->replace($id, $doc);
    }
}
