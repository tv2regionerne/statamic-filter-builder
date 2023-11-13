<?php
use Illuminate\Support\Facades\Route;

Route::prefix('filter-builder/api')->name('filter-builder.api.')->group(function() {
    require('api.php');
});