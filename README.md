# mysof/password-check
This is a simple class to check if a password is clean utilising havibeenpwned.com API.

This API does not require authentication for password checks.

## Example Usage

This example utilises the Symfony HTTP Client, however any other client implementing \Symfony\Contracts\HttpClient\HttpClientInterface could be used in its place.

```
$checker = new \MySof\PasswordCheck(
    \Symfony\Component\HttpClient\HttpClient::create()
);

if ($checker->isSafe("password")) {
    print "Password is clean";
} else {
    print "Password has been pwned";
}
```

For further details of the API see https://haveibeenpwned.com/API/v3#PwnedPasswords
