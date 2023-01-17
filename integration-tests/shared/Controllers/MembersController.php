<?php

namespace Shared\Controllers;

use Illuminate\Http\Response;

class MembersController extends Controller
{
    public function create(): Response
    {
        return new Response(__METHOD__, Response::HTTP_CREATED);
    }
}
