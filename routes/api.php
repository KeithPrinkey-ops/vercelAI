<?php

use App\Http\Controllers\StreamChatController;
use Illuminate\Support\Facades\Route;

Route::post('/chat', StreamChatController::class);
