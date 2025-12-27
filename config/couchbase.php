<?php

return [
    'conn_string' => env('COUCHBASE_CONN_STRING', ''),
    'username' => env('COUCHBASE_USERNAME', ''),
    'password' => env('COUCHBASE_PASSWORD', ''),
    'bucket' => env('COUCHBASE_BUCKET', ''),
    'scope' => env('COUCHBASE_SCOPE', '_default'),
    'collection' => env('COUCHBASE_COLLECTION', 'tickets'),
];
