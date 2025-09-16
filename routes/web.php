<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('test-faker', function () {
    $faker = \Faker\Factory::create('ar_SA'); // استخدام اللغة العربية السعودية
    return [
        'name' => $faker->name,
        'address' => $faker->address,
        'text' => $faker->text,
        'email' => $faker->unique()->safeEmail,
        'phone' => $faker->phoneNumber,
        'paragraph' => $faker->paragraph,
        'description' => $faker->paragraphs(3, true),
    ];
});
