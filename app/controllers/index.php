<?php
/**
 * Author: Paul Bardack paul.bardack@gmail.com http://paulbardack.com
 * Date: 06.08.14
 * Time: 19:53
 */

use Builder\SchRequest;
use Builder\SchResponse;

$app->router()->get(
    '/latest.html',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        $view = [
        ];
        return $app->view()->render('latest.twig', $view);
    }
);

$app->router()->get(
    '/history.html',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        $limit = 20;
        $offset = 0;
        if ($request->page and $request->page > 1) {
            $offset = $limit * $request->page - $limit;
        }

        $structure = $app->build_table()->getFieldsStructure();
        unset($structure[array_search('comment', $structure)]);

        return $app->view()->render(
            'history.twig',
            [
                'page' => $request->page,
                'build_table' => $app->build_table(),
                'builds' => $app->build_table()->getList($limit, $offset),
                'structure' => $structure
            ]
        );
    }
);

$app->router()->get(
    '/api.html',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        return $app->view()->render('api.twig', ['api' => \Michelf\MarkdownExtra::defaultTransform(file_get_contents(PROJECT_DIR . '/README.md'))]);
    }
);

$app->router()->get(
    '/download/[h:id]',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        $build = $app->build_table()->getById($request->id);

        if (!$build) {
            $response->jsonError(404, "Record not found");
        }

        $file = sprintf(
            "%s/public/builds/%s/%s",
            PROJECT_DIR,
            $build[\Builder\Model\BuildTable::MONGO_FIELD_NAME_ID],
            $build['build_filename']
        );

        if (file_exists($file)) {
            // ToDo: something wrong with this
            $response->file($file);
        } else {
            $response->jsonError(404, "File not found");
        }

    }
);

$app->router()->post(
    '/upload',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'api')) !== true) {
            return $acl_response;
        }

        $build = $app->build_table();
        $request->validateParams($build->getValidatedFields(), $response);

        if (!$request->files() or !$request->files()->exists('build_file')) {
            return $response->jsonError(422, "Specify .ipa file with name 'build_file'");
        }

        $result = $build->create($request->params(), $request->files()->build_file);
        if ($result['success'] !== true) {
            $response->jsonError($result['code'], $result['error']);
        } else {
            $response->jsonOk($result);
        }
    }
);

$app->router()->get(
    '/latest.json',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'api')) !== true) {
            return $acl_response;
        }

        $limit = $request->limit ? : 5;
        $offset = $request->offset ? : 0;

        $response->jsonOk($app->build_table()->getList($limit, $offset, true));
    }
);

$app->router()->get(
    '/build/delete/[h:id]',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if(($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        $app->build_table()->deleteById($request->id);
        /** ToDo: delete file */

        $response->redirect('/history.html');
    }
);

