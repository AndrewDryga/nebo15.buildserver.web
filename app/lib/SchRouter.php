<?php

namespace Builder;

class SchRouter extends \Klein\Router
{
    public function dispatch(
        \Klein\Request $request = null,
        \Klein\AbstractResponse $response = null,
        $send_response = true,
        $capture = self::DISPATCH_NO_CAPTURE
    ) {
        $request = $request ? : SchRequest::createFromGlobals();
        $response = $response ? : new SchResponse();

        return parent::dispatch($request, $response, $send_response, $capture);
    }
}
