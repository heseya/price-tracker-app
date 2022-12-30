<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Resources\ErrorResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    private const ERRORS = [
        AuthenticationException::class => [
            'message' => 'Unauthenticated',
            'code' => Response::HTTP_UNAUTHORIZED,
        ],
        AuthorizationException::class => [
            'message' => 'Unauthorized',
            'code' => Response::HTTP_FORBIDDEN,
        ],
        NotFoundHttpException::class => [
            'message' => 'Page not found',
            'code' => Response::HTTP_NOT_FOUND,
        ],
        ModelNotFoundException::class => [
            'message' => 'Page not found',
            'code' => Response::HTTP_NOT_FOUND,
        ],
        MethodNotAllowedHttpException::class => [
            'message' => 'Page not found',
            'code' => Response::HTTP_NOT_FOUND,
        ],
        ValidationException::class => [
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
        ApiConnectionException::class => [
            'code' => Response::HTTP_SERVICE_UNAVAILABLE,
        ],
        ApiServerErrorException::class => [
            'code' => Response::HTTP_SERVICE_UNAVAILABLE,
        ],
        ApiClientErrorException::class => [
            'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
        ],
        ApiAuthenticationException::class => [
            'code' => Response::HTTP_FORBIDDEN,
        ],
        ApiAuthorizationException::class => [
            'code' => Response::HTTP_FORBIDDEN,
        ],
        InvalidTokenException::class => [
            'message' => 'Auth token is invalid',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ],
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, \Throwable $e): Response
    {
        $class = $e::class;

        if (array_key_exists($class, self::ERRORS)) {
            $error = new Error(
                self::ERRORS[$class]['message'] ?? $e->getMessage(),
                self::ERRORS[$class]['code'] ?? 500,
                method_exists($e, 'errors') ? $e->errors() : [],
            );
        } else {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }

            if (true === config('app.debug')) {
                return parent::render($request, $e);
            }

            $error = new Error();
        }

        return ErrorResource::make($error)
            ->response()
            ->setStatusCode($error->code);
    }
}
