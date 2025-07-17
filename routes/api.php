<?php
use App\Http\Controllers\FaceRecognitionController;

Route::get('/face-recognition', function () {
    return view('face_recognition'); // Pastikan nama file Blade sesuai
});