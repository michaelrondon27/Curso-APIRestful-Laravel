<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{

    use ApiResponser;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {

        if ( $exception instanceof ModelNotFoundException ) {

            $modelo = strtolower( class_basename( $exception->getModel() ) );
            return $this->errorResponse("No Existe ninguna instancia de {$modelo} con el id especificado", 404);

        }

        if ( $exception instanceof AuthorizationException ) {

            return $this->errorResponse( 'No autenticado.', 401 );

        }

        if ( $exception instanceof AuthenticationException ) {

            return $this->errorResponse( 'No posse permisos para ejecutar esta acción.', 403 );

        }

        return parent::render($request, $exception);

    }
}
