<?php

namespace App\Exceptions;

use App\Helper\ResponseHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

  public function render($request, $e)
  {
    if ($e instanceof NotFoundHttpException) {
      return ResponseHelper::error([
        'message' => 'Route Not Found',
      ]);
    }
    else if ($e) {
      return ResponseHelper::error([
        'error_message' => $e->getMessage(),
      ]);
    }

    return parent::render($request, $e);
  }
}
