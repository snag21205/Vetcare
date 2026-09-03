<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Đăng ký middleware của Spatie Laravel Permission
        // 'role' và 'permission' là tên alias dùng trong routes/web.php
        $middleware->alias([
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->respond(function (Response $response, \Throwable $e, Request $request) {
            $status = $response->getStatusCode();

            if (in_array($status, [400, 401, 403, 404, 419, 500, 503])) {
                // Xử lý 419 CSRF Token mismatch
                if ($status === 419) {
                    return back()->with([
                        'error' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại.',
                    ]);
                }

                // Nếu là lỗi 500 trong môi trường local và APP_DEBUG=true thì để Laravel hiển thị stack trace chi tiết
                if ($status === 500 && config('app.debug') && app()->environment('local')) {
                    return $response;
                }

                // Render trang Error.vue qua Inertia
                return Inertia::render('Error', [
                    'status'  => $status,
                    'message' => $e->getMessage() ?: null,
                ])->toResponse($request)->setStatusCode($status);
            }

            return $response;
        });
    })->create();

