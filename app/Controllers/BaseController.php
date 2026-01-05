<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseController extends Controller
{
    /**
     * Preload any helpers here.
     *
     * @var list<string>
     */
    protected $helpers = ['url', 'session'];

    /**
     * Constructor.
     */
    public function initController(\CodeIgniter\HTTP\IncomingRequest $request, ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }
}
