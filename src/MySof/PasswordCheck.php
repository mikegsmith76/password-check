<?php

namespace MySof;

use MySof\PasswordCheck\Exception\NoResponse as NoResponseException;
use MySof\PasswordCheck\Exception\UnexpectedResponse as UnexpectedResponseException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class PasswordCheck
 *
 * @author Mike Smith <mail@mikegsmith.co.uk>
 * @package MySof
 */
class PasswordCheck
{
    protected $httpClient;
    protected $uri = "https://api.pwnedpasswords.com/range/%s";

    public function __construct(
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    public function isSafe(string $password): bool
    {
        $hashedPassword = strtoupper(sha1($password));

        $prefix = substr($hashedPassword, 0, 5);
        $suffix = substr($hashedPassword, 5);

        $response = $this->httpClient->request(
            "GET",
            sprintf($this->uri, $prefix)
        );

        try {
            $statusCode = $response->getStatusCode();
            $content = $response->getContent();

        } catch (TransportExceptionInterface $exception) {
            throw new NoResponseException();
        }

        if (200 !== $statusCode) {
            throw new UnexpectedResponseException();
        }

        return false === strpos($content, $suffix);
    }
}
