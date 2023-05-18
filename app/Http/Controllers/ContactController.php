<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactUpdateRequest;
use Illuminate\Http\Request;
use App\Interfaces\SalesforceRepositoryInterface;
use App\Interfaces\ContactRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Inertia\Response as InertiaResponse;
use Inertia\Inertia;

class ContactController extends Controller
{
    private SalesforceRepositoryInterface $salesforceRepository;
    private ContactRepositoryInterface $contactRepository;

    /**
     * ContactController constructor.
     * @param SalesforceRepositoryInterface $salesforceRepository
     * @param ContactRepositoryInterface $contactRepository
     */
    public function __construct(
        SalesforceRepositoryInterface $salesforceRepository,
        ContactRepositoryInterface $contactRepository
    )
    {
        $this->salesforceRepository = $salesforceRepository;
        $this->contactRepository = $contactRepository;
    }

    /**
     * @return InertiaResponse
     */
    public function index(): InertiaResponse {
        return Inertia::render('Contacts/List');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        $this->salesforceRepository->fetchToken();
        $totalContacts = $this->contactRepository->countTotalContacts();
        $contacts = $this->contactRepository->fetchContacts($request->query());
        $response = array_merge($contacts, [
            'totalContacts' => $totalContacts['totalSize'] ?? 0
        ]);

        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * @param ContactUpdateRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(ContactUpdateRequest $request, $id): JsonResponse
    {
        $this->salesforceRepository->fetchToken();
        $response = $this->contactRepository->updateContact($id, $request->all());

        return response()->json($response, Response::HTTP_OK);
    }
}
