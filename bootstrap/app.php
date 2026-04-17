<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // MENGATUR TUJUAN REDIRECT JIKA USER BELUM LOGIN / SESSION HABIS
        $middleware->redirectGuestsTo(function (Request $request) {
            
            // DAFTAR VIP ADMIN: Semua URL yang dikhususkan untuk Admin/Approver
            if (
                $request->is('dashboard-admin*') || 
                $request->is('kelola-*') ||         // Menangkap kelola-admin & kelola-satker
                $request->is('maintenance*') || 
                $request->is('profil-admin*') ||
                $request->is('pengajuan*')          // Menangkap pengajuan dan semua filternya
            ) {
                return route('admin.login'); // Lempar ke Login Admin
            }

            // DEFAULT: Jika bukan URL di atas, lempar ke Login Satker
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();