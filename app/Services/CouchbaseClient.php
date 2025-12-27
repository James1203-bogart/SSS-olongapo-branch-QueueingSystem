<?php

namespace App\Services;

class CouchbaseClient
{
    private $cluster;
    private $bucket;
    private $scope;
    private $collection;

    public function __construct()
    {
        $cfg = config('couchbase');
        $conn = $cfg['conn_string'] ?? '';
        $user = $cfg['username'] ?? '';
        $pass = $cfg['password'] ?? '';
        $bucketName = $cfg['bucket'] ?? '';
        $scopeName = $cfg['scope'] ?? '_default';
        $collectionName = $cfg['collection'] ?? 'tickets';

        if (!class_exists('Couchbase\\Cluster')) {
            throw new \RuntimeException('Couchbase PHP SDK not installed. Please install the pecl couchbase extension.');
        }

        $this->cluster = new \Couchbase\Cluster($conn, new \Couchbase\ClusterOptions($user, $pass));
        $this->bucket = $this->cluster->bucket($bucketName);
        $this->scope = $this->bucket->defaultScope();
        try {
            $this->scope = $this->bucket->scope($scopeName);
        } catch (\Throwable $e) {
            // fallback to default scope
        }
        $this->collection = $this->scope->collection($collectionName);
    }

    public function collection()
    {
        return $this->collection;
    }

    public function query(string $statement, array $params = [])
    {
        return $this->cluster->query($statement, ['parameters' => $params]);
    }
}
