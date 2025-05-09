<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->group(function () {
    // Get all knowledge base
    Route::get('/knowledge-base', function () {
        try {
            $client = new MongoDB\Client(env('MONGODB_URI'));
            $collection = $client->selectCollection(env('MONGODB_DATABASE'), 'knowledge_base');
            return response()->json($collection->find()->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    // Get all FAQs
    Route::get('/faqs', function () {
        try {
            $client = new MongoDB\Client(env('MONGODB_URI'));
            $collection = $client->selectCollection(env('MONGODB_DATABASE'), 'faqs');
            return response()->json($collection->find()->toArray());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });
});
