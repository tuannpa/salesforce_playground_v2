<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalesforceAccountConnectRequest;
use App\Interfaces\SalesforceServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Inertia\Inertia;

class HomeController extends Controller
{
    private SalesforceServiceInterface $salesforceSOAPService;

    /**
     * @param SalesforceServiceInterface $salesforceSOAPService
     */
    public function __construct(SalesforceServiceInterface $salesforceSOAPService)
    {
        $this->salesforceSOAPService = $salesforceSOAPService;
    }

    /**
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Home/Index', [
            'loginCryptoConfig' => config('crypto.salesforce_login')
        ]);
    }

    /**
     * @param SalesforceAccountConnectRequest $request
     * @return JsonResponse
     */
    public function connectSFDCAccount(SalesforceAccountConnectRequest $request): JsonResponse
    {
        $username = $request->get('Username');
        $password = $request->get('Password');
        $securityToken = $request->get('Token');

        $response = $this->salesforceSOAPService->authenticate($username, $password, $securityToken);

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @return JsonResponse
     */
    public function logoutSalesforceAccount(): JsonResponse
    {
        try {
            $response = $this->salesforceSOAPService->accountLogout();
        } catch (\Exception $exception) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }

        return response()->json($response, Response::HTTP_OK);
    }
}
