<?php

return [
    'authenticationBaseUri' => env('SALESFORCE_BASE_AUTHENTICATION_URI', 'https://login.salesforce.com'),
    'tokenUri' => env('SALESFORCE_BASE_AUTHENTICATION_URI', 'https://login.salesforce.com') . '/services/oauth2/token',
    'revokeUri' => env('SALESFORCE_BASE_AUTHENTICATION_URI', 'https://login.salesforce.com') . '/services/oauth2/revoke',
    'clientId' => env('SALESFORCE_CLIENT_ID'),
    'clientSecret' => env('SALESFORCE_CLIENT_SECRET'),
    'username' => env('SALESFORCE_USERNAME'),
    'password' => env('SALESFORCE_PASSWORD'),
    'securityToken' => env('SALESFORCE_SECURITY_TOKEN'),
    'entityService' => env('SALESFORCE_SERVICE') . '/sobjects',
    'queryService' => env('SALESFORCE_SERVICE') . '/query',
    'bulkApiService' => env('SALESFORCE_SERVICE') . '/jobs/query'
];
