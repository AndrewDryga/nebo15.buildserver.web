<?php

namespace Builder;

class SchRequest extends \Klein\Request
{
    public function login()
    {
        return $this->server->get('PHP_AUTH_USER');
    }

    public function password()
    {
        return $this->server->get('PHP_AUTH_PW');
    }

    public static function createFromGlobals()
    {
        $content_type_json = explode(';', $_SERVER['CONTENT_TYPE'])[0] == 'application/json';
        if ($content_type_json) {
            $json_input = json_decode(file_get_contents('php://input'), true);
        }
        return new static(
            $_GET,
            $content_type_json ? ($json_input ? : []) : $_POST,
            $_COOKIE,
            $_SERVER,
            $_FILES,
            null
        );
    }

    public function param($key, $default = null)
    {
        if ($value = parent::param($key, $default)) {
            return $value;
        }
        if (!$json = json_decode($this->body())) {
            return $default;
        }
        if (property_exists($json, $key)) {
            return $json->$key;
        }
    }

    public function validateParams($params, SchResponse $response)
    {
        $invalid_params = [];
        foreach ($params as $param) {
            if (is_null($this->param($param))) {
                $invalid_params[] = $param;
            }
        }
        if (!count($invalid_params)) {
            return true;
        }
        $invalid_params = implode(', ', $invalid_params);
        $response->jsonError(403, "Fields " . $invalid_params . ' required');

        return false;
    }
}
