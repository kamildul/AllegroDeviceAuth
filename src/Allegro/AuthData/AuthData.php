<?php

namespace KamilDul\CategorySynchro\Allegro\AuthData;

/**
 * Class AuthData
 *
 * A class representing authentication data (access token, token type, expiration time, scope).
 */
class AuthData
{
    private string $accessToken;
    private string $tokenType;
    private int $expiresIn;
    private string $scope;
    private string $refreshToken; // Dodane pole refreshToken

    /**
     * AuthData constructor.
     *
     * Initializes the AuthData object by setting default values to empty strings (for strings) and zero (for int).
     */
    public function __construct()
    {
        $this->accessToken = '';
        $this->tokenType = '';
        $this->expiresIn = 0;
        $this->scope = '';
        $this->refreshToken = ''; // Dodane pole refreshToken
    }

    /**
     * Get the value of the access token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Set the value of the access token.
     *
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Get the value of the token type.
     *
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * Set the value of the token type.
     *
     * @param string $tokenType
     */
    public function setTokenType(string $tokenType): void
    {
        $this->tokenType = $tokenType;
    }

    /**
     * Get the expiration time of the access token.
     *
     * @return int
     */
    public function getExpiresIn(): int
    {
        return $this->expiresIn;
    }

    /**
     * Set the expiration time of the access token.
     *
     * @param int $expiresIn
     */
    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * Get the access scope.
     *
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    /**
     * Set the access scope.
     *
     * @param string $scope
     */
    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    /**
     * Get the value of the refresh token.
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * Set the value of the refresh token.
     *
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }
}

?>
