<?php

declare(strict_types=1);

\Illuminate\Support\Facades\Route::apiResource('contacts', \App\Containers\Contacts\Controllers\ContactsApiController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->parameters([
        'contacts' => 'contactId',
    ]);
