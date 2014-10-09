<?php
/**
 * Author: Paul Bardack paul.bardack@gmail.com http://paulbardack.com
 * Date: 06.08.14
 * Time: 19:53
 */

use Builder\SchRequest;
use Builder\SchResponse;

$app->router()->get(
    '/',
    function (SchRequest $request, SchResponse $response) use ($app) {
        $response->redirect('/latest');
    }
);

$app->router()->get(
    '/latest',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if (($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        $view = [
        ];

        return $app->view()->render('latest.twig', $view);
    }
);

$app->router()->get(
    '/latest.json',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if (($acl_response = $app->acl($request, $response, 'api')) !== true) {
            return $acl_response;
        }

        $limit = $request->limit ? : 5;
        $offset = $request->offset ? : 0;

        $response->jsonOk($app->build_table()->getList($limit, $offset, true));
    }
);

$app->router()->get(
    '/history',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if (($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        // Getting data collection
        $build_table = $app->build_table();

        $available_fields = $build_table->getFieldsStructure();
        $available_fields['created_at'] = [];
        $available_fields['id'] = [];
        // Filtering
        $filters = $request->paramsGet()->all();
        foreach ($filters as $field_key => $field_value) {
            if(!array_key_exists($field_key, $available_fields)) {
                if($field_key == 'id') {
                    $field_key = '_id';
                }
                unset($filters[$field_key]);
            }
        }

        // Ordering
        $sort = [];
        $sort_key = $request->paramsGet()->order_by ?: 'created_at';
        $sort_order = $request->paramsGet()->order ?: 'desc';
        if($sort_key) {
            if(array_key_exists($sort_key, $available_fields)) {
                if($sort_key == 'id') {
                    $sort_key = '_id';
                }
                $sort[$sort_key] = ($sort_order == 'desc' ? -1 : 1);
            }
        }

        // Pagination
        $limit = 20;
        $offset = 0;
        $page = $request->page ?: 1;
        if ($request->page && $request->page > 1) {
            $offset = $limit * $request->page - $limit;
        }

        // Getting data
        $builds = $build_table->getList($limit, $offset, false, $filters, $sort, $group);

        $pages_count = ceil($builds->count() / $limit);


        // Data export
        return $app->view()->render('history.twig', [
                'page' => $page,
                'pages_count' => $pages_count,
                'build_table' => $build_table,
                'builds' => $builds,
            ]
        );
    }
);

$app->router()->get(
    '/api',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if (($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        return $app->view()->render('api.twig', ['api' => \Michelf\MarkdownExtra::defaultTransform(file_get_contents(PROJECT_DIR . '/README.md'))]);
    }
);

$app->router()->get(
    '/download/[h:id]',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if (($acl_response = $app->acl($request, $response, 'web')) !== true) {
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
            // ToDo: something wrong with this (c) bardack
            $response->file($file);
        } else {
            $response->jsonError(404, "File not found");
        }

    }
);

$app->router()->get(
    '/builds/[h:id]',
    function (SchRequest $request, SchResponse $response) use ($app) {
        var_dump($request->id);
        $response->redirect('/history?id=' + $request->id);
    }
);

$app->router()->get(
    '/builds/[h:id]/delete',
    function (SchRequest $request, SchResponse $response) use ($app) {
        if (($acl_response = $app->acl($request, $response, 'web')) !== true) {
            return $acl_response;
        }

        $app->build_table()->deleteById($request->id);

        $back_url = $request->server()->HTTP_REFERER;
        if (!$back_url) {
            $back_url = '/history';
        }
        $response->redirect($back_url);
    }
);
