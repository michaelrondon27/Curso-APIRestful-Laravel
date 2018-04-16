<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Asm89\Stack\CorsService;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
        $response = $this->handleException($request, $exception);

        app(CorsService::class)->addActualRequestHeaders($response, $request);

        return $response;

    }

    public function handleException($request, Exception $exception)
    {
        if ( $exception instanceof ModelNotFoundException ) {

            $modelo = strtolower( class_basename( $exception->getModel() ) );
            return $this->errorResponse("No Existe ninguna instancia de {$modelo} con el id especificado", 404);

        }

        if ( $exception instanceof  AuthenticationException) {

            if ( $this->isFrontend($request) ) {
                return redirect()->guest('login');
            }

            return $this->errorResponse( 'No autenticado.', 401 );

        }

        if ( $exception instanceof AuthorizationException ) {

            return $this->errorResponse( 'No posse permisos para ejecutar esta acción.', 403 );

        }

        if ( $exception instanceof NotFoundHttpException ) {

            return $this->errorResponse( 'No encontró la url especificada.', 404 );

        }

        if ( $exception instanceof MethodNotAllowedHttpException ) {

            return $this->errorResponse( 'El método especificado en la petición no es válido.', 405 );

        }

        if ( $exception instanceof HttpException ) {

            return $this->errorResponse( $exception->getMessage(), $exception->getStatusCode() );

        }

        if ( $exception instanceof QueryException ) {

            $codigo = $exception->errorInfo[1];

            if ( $codigo == 1451 ) {
                return $this->errorResponse( 'No se puede eliminar de forma permanente el recurso porque está relacionado con algún otro.', 409 );
            }

        }

        if ( $exception instanceof TokenMismatchException ) {
            return redirect()->back()->withInput($request->input());
        }

        if ( config('app.debug') ) {

            return parent::render($request, $exception);

        }

        return $this->errorResponse( 'Falla inesperada. Intente Luego', 500 );
    }

    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }
}
