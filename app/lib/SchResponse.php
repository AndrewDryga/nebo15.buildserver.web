<?php

namespace Builder;

class SchResponse extends \Klein\Response
{
    public function jsonOk($object, $page_path = null, $page_offset = null, array $meta_data = array(), $options = 0)
    {
        $meta_data["code"] = 200 ;
        $data = [
            "meta" => $meta_data,
            "data" => $object,
            "pagination" => [
                "next_url" => 'http' . (empty($_SERVER['HTTPS']) ? '' : 's') . '://' . $_SERVER['HTTP_HOST'] . $page_path,
                "offset" => $page_offset
            ]
        ];
        if (!$page_path && !$page_offset) {
            unset($data['pagination']);
        }

        $this->code(200);

        return $this->json($data, null, $options);
    }

    public function jsonNotAuthorized()
    {
        return $this->jsonError(401, 'Login, please', 'Unauthorized');
    }

    public function jsonError($code, $message, $type = null)
    {
        $data = [
            "meta" => [
                "code" => $code,
                "error_type" => $type,
                "error_message" => $message
            ],
            "data" => null
        ];
        $this->code($code);

        return $this->json($data);
    }

    public function jsonProxy($mserver_response)
    {
        $this->code($mserver_response->meta->code);

        return $this->json($mserver_response);
    }

    public function json($object, $jsonp_prefix = null, $options = 0)
    {
        $options = $options ?: JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

        return parent::json($object, $jsonp_prefix, $options);
    }
}
