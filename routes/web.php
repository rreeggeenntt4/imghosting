<?php

use Illuminate\Support\Facades\Route;
/* use App\Http\Controllers;
use App\Http\Controllers\ImageController; */
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/upload', [App\Http\Controllers\ImageController::class, 'showUploadForm'])->name('upload.form');
Route::post('/upload', [App\Http\Controllers\ImageController::class, 'upload'])->name('upload.submit');
Route::get('/images', [App\Http\Controllers\ImageController::class, 'showImages'])->name('images.show');

Route::get('/download/{filename}', [App\Http\Controllers\ImageController::class, 'downloadZip'])->name('download.file');
