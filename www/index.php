<?php

/**
 * @var Builder\App Application class
 */
$app = require_once __DIR__ . '/../app/bootstrap.php';
$app->router()->dispatch();
