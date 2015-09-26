<?php
namespace MyApp\Controller;

use Symfony\Component\HttpFoundation\Request;

class Test
{

    public function test(Request $request)
    {
        $params = $request->request->all();
        return [
            'params' => $params
        ];
    }
}
