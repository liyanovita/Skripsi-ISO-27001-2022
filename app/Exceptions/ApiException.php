<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Custom API Exception for standardized error responses
 */
class ApiException extends Exception
{
    protected int $statusCode;
    protected array $errors;

    /**
     * Create a new API Exception instance
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $errors Additional error details
     * @param Exception|null $previous Previous exception
     */
    public function __construct(
        string $message = 'An error occurred',
        int $statusCode = 500,
        array $errors = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Get the HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get additional error details
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Render the exception as JSON response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
            'errors' => $this->errors,
        ], $this->statusCode);
    }

    /**
     * Create a validation exception (422)
     */
    public static function validation(string $message, array $errors = []): self
    {
        return new self($message, 422, $errors);
    }

    /**
     * Create a not found exception (404)
     */
    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self($message, 404);
    }

    /**
     * Create an unauthorized exception (401)
     */
    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new self($message, 401);
    }

    /**
     * Create a forbidden exception (403)
     */
    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new self($message, 403);
    }

    /**
     * Create a conflict exception (409)
     */
    public static function conflict(string $message = 'Resource conflict'): self
    {
        return new self($message, 409);
    }

    /**
     * Create an internal server error exception (500)
     */
    public static function internalError(string $message = 'Internal server error'): self
    {
        return new self($message, 500);
    }
}