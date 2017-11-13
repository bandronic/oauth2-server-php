# OAuth2Server

Implementation of the OAuth2 server using Doctrine ORM. 

The package forks [BShaffer's OAuth2](bshaffer/oauth2-server-php) implementation and adapts it to use Doctrine, PSR4 and PSR7.

## Requirements

bshaffer/oauth2-server-php

dasprid/container-interop-doctrine

## Installation

1. Add the ConfigProvider to the config/container.php file:

    ```php
    <?php
    
    ...
    $aggregator = new ConfigAggregator([
        ...
        \OAuth2Server\ConfigProvider::class,
        ...
    ], $cacheConfig['config_cache_path']);
    ```

2. In the config/autoload/local.php add an array for oauth2:

    ```php
    
    return [
        ...
        'oauth2' => [
                'db' => [
                    'dsn'      => '', // For example "mysql:dbname=oauth2_db;host=localhost"
                    'username' => '', // Database username
                    'password' => '', // Database password
                ],
                'allow_implicit'    => true, // Default (set to true when you need to support browser-based or mobile apps)
                'access_lifetime'   => 3600, // Default (set a value in seconds for access tokens lifetime)
                'enforce_state'     => true,  // Default
                'always_issue_new_refresh_token' => true, // Set to true in order to receive a refresh token always
                'keys_folder' => './config/keys', // Public and private keys folder location
                'user_entity'       => '', // MANDATORY user entity, must implement UserInterface
                'client_service'       => '', // OPTIONAL client service, must implement ClientInterface
            ],
            ...
    
    ];
    
    ```
    
    Alternatively you can copy the data\oauth2.local.php.dist file in the config/autoload folder and change the values inside it accordingly.
    
    You must specify an entity for the user_entity config entry. The user_entity class must implement the interface:
    
    ```php
    OAuth2Server\Entity\UserInterface
    ```
    
    Optional, if you wish to change the get client details, scope or grant type check, 
    you can specify a client service in the client_service settings parameter. The client service must implement the interface
    
    ```php
    OAuth2\Storage\ClientInterface
    ```

3. Create a folder keys in the config folder and inside it generate the private and public keys:
    
    1. Create a private key openssl genrsa -out private.key 1024
    2. Create a public key openssl rsa -in private.key -pubout > public.key
    
    Alternatively, you can copy the pre-generated keys from the data folder
    
    The pre-generated key were generated without a password.
    
    Alternatively, you can generate the key anywhere in the project structure and specify the location of the keys in the config array
    under the key 'keys_folder'
    
## Usage

In the routes.php file add the following entries:

```php

$app->post('/authorize', \OAuth2Server\Middleware\Authorize::class, 'authorize');
$app->post('/access_token', \OAuth2Server\Middleware\Token::class, 'access_token');

```

Create the database schema using the included migration file: 20171107115657_oauth.php

Add a client: 

```
client_id: test
client_secret: test 
grant_types: authorization_code password refresh_token
```

### Authorization

For the authorize method, performing a ```GET``` on the 

```
http://localhost:8080/authorize?client_id=test&response_type=code&state=asdf123&redirect_uri=http%3A%2F%2Flocalhost:8080%2Fauthorize
```

will validate the URL and redirect to an authorize/deny page

Authorizing will redirect to the provided redirect URI with an authorization token

Posting to the ```/authorize``` URI with the following body fields:

```

Postman:

[
    {"key":"grant_type","value":"authorization_code","description":""},
    {"key":"client_id","value":"test","description":""},
    {"key":"client_secret","value":"test","description":""},
    {"key":"scope","value":"test","description":""},
    {"key":"code","value":"<AUTHORIZATION_CODE>","description":""},
    {"key":"redirect_uri","value":"<URL>","description":""}
]
    
```

will return a valid token which you can use multiple times, example:

```json
{
    "access_token": "7b2d00806e938ad976071c4d4d5cd1fe6bc680e9",
    "expires_in": 3600,
    "token_type": "Bearer",
    "scope": "test",
    "refresh_token": "471cae69cf042921348f284881de0477528017c7"
}
```

### Password

Add a user with 
```
username: test
password: test
```

For the password method, performing a ```POST``` on the ```/access_token``` route with the following body:

```
Postman

[
  {"key":"grant_type","value":"password","description":""},
  {"key":"client_id","value":"test","description":""},
  {"key":"client_secret","value":"test","description":""},
  {"key":"username","value":"test","description":""},
  {"key":"password","value":"test","description":""},
  {"key":"scope","value":"test","description":""}
]
```

will return an access token, example:

```json
{
    "access_token": "0d5e4bf51129e0fe2c94f9ecb91786ffab0018b2",
    "expires_in": 3600,
    "token_type": "Bearer",
    "scope": "test",
    "refresh_token": "1f67452221c02257ca39f7934906bf92db8cd51f"
}
```

### Access token usage

Piping the ```\OAuth2Server\Middleware\VerifyResource``` middleware to a route will verify the access token validity

```php
$app->get('/', [ \OAuth2Server\Middleware\VerifyResource::class, App\Action\HomePageAction::class ], 'home');
```

A valid ```GET``` for 
```
http://localhost:8080/
``` 

would have to contain the Authorization header. Example:

```
Authorization: Bearer <TOKEN>
```