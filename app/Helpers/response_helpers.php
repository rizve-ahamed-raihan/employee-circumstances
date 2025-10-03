<?php

use Illuminate\Http\JsonResponse;

if (! function_exists('response_unprocessable')) {
    function response_unprocessable($errors = null, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json(['status'=>'error','message'=>$message,'errors'=>$errors], 422);
    }
}

if (! function_exists('response_created')) {
    function response_created($data, string $message = 'Created successfully'): JsonResponse
    {
        return response()->json(['status'=>'success','message'=>$message,'data'=>$data], 201);
    }
}

if (! function_exists('response_ok')) {
    function response_ok($data, string $message = 'Success'): JsonResponse
    {
        return response()->json(['status'=>'success','message'=>$message,'data'=>$data], 200);
    }
}

if (! function_exists('response_no_content')) {
    function response_no_content(): JsonResponse
    {
        return response()->json(null, 204);
    }
}

if (! function_exists('response_forbidden')) {
    function response_forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json(['status'=>'error','message'=>$message], 403);
    }
}
