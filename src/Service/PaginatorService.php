<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PaginatorService
{
    public function getPageAndLimit(Request $request): array
    {
        $limit = (int)($request->query->get('limit') ?? 10);
        $page = (int)($request->query->get('page') ?? 1);

        return ([max($page, 1), max($limit, 1)]);
    }

}
