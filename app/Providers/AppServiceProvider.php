<?php

namespace App\Providers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Response::macro('errorJson', function (string $message, int $statusCode = 200, array|object $debugData = null, string|int $code = null) {
        //     return response()->json([
        //         'success' => false,
        //         'error' => [
        //             'code' => $code,
        //             'message' => $message,
        //             'debugData' => $debugData
        //         ]
        //     ], $statusCode);
        // });

        Response::macro('failedValidationJson', function (Validator $validator) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'data' => [
                    'errors' => $validator->errors(),
                ]
            ], 422));
        });

        // Response::macro('successJson', function ($data, int $statusCode = 200) {
        //     if ($data instanceof ResourceCollection && $data->resource instanceof AbstractPaginator) {
        //         $paginatedData = $data->toResponse(request())->getData();

        //         return response()->json([
        //             'success' => true,
        //             'data' => $paginatedData->data,
        //             'links' => $paginatedData->links,
        //             'meta' => $paginatedData->meta,
        //         ], $statusCode);
        //     }

        //     return response()->json([
        //         'success' => true,
        //         'data' => $data,
        //     ], $statusCode);
        // });

        Response::macro('tokenJson', function (string $token) {
            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);
        });
    }
}
