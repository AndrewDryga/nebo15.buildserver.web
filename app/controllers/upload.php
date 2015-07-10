<?php
/**
 * Author: Paul Bardack paul.bardack@gmail.com http://paulbardack.com
 * Date: 06.08.14
 * Time: 19:53
 */

use Builder\SchRequest;
use Builder\SchResponse;

$app->router()->post(
    '/upload.json',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'api')) !== true) {
            return $acl_response;
        }

        $build = $app->build_table();
        $request->validateParams($build->getValidatedFields(), $response);

        if (!$request->files() or !$request->files()->exists('build_file')) {
            return $response->jsonError(422, "Specify .ipa file with name 'build_file'");
        }
        $app_file = ($request->files()->build_app_file) ? $request->files()->build_app_file : [];
        $result = $build->create($request->params(), $request->files()->build_file, $app_file);
        if ($result['code'] !== 200 || is_null($result['data'])) {
            $response->jsonError($result['code'], $result['error']);
        } else {
            $response->jsonOk($result['data']);
        }
    }
);
