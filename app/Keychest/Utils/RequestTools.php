<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.09.17
 * Time: 14:02
 */

namespace App\Keychest\Utils;


use Illuminate\Http\Request;
use ReflectionClass;

class RequestTools
{
    /**
     * Tries to reset acceptableContentTypes protected property of the request with use of reflection.
     *
     * This approach is a bit hacky but in the middleware the request can be only modified, we cannot
     * create a new request object and pass that for processing, the controller will get the original instance.
     *
     * Using reflection is also fragile, this may fail - watch for exceptions.
     * @param Request $request
     */
    public static function tryResetAcceptableContentTypes(Request $request){
        $reflectionClass = new ReflectionClass(Request::class);
        $reflectionProperty = $reflectionClass->getProperty('acceptableContentTypes');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($request, null);
    }

    /**
     * Reinitializes the request with the current data so all cached attributes are wiped out
     * and recomputed on next query.
     *
     * Used e.g., for changing acceptableContentTypes from Accept header.
     * @param Request $request
     */
    public static function resetCachedFields(Request $request){
        // Workaround is to reinit the request with old request data.
        $oldRequest = clone $request;

        $request->initialize([], [], [], [], [], [], null);
        $request->request = $oldRequest->request;
        $request->query = $oldRequest->query;
        $request->attributes = $oldRequest->attributes;
        $request->cookies = $oldRequest->cookies;
        $request->files = $oldRequest->files;
        $request->server = $oldRequest->server;
        $request->headers = $oldRequest->headers;
    }

}