# API description

## Claim access token

First you need to obtain an access token / API key for the service. 

Required parameters are:

- `email` email address
- `api_key` at least 16 randomly generated characters. Ideally an UUID.

Example GET request:

```
https://keychest.net/api/v1.0/access/claim?email=your@email.com&api_key=5b9b6ace-b950-11e7-bb9f-7fca73a26228
```

If the account with given email is not registered it will be created.

`api_key` is the client identification. For the security reasons each client
 should have unique `api_key` so user can audit changes and revoke particular `apikey`s. 
 
For `certbot` it can be `user_id.istance_id`.



