<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([
    'middleware' => ['guest'],
], function () {
    Route::get('/', [CourseController::class, 'index'])->name('home');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
});
