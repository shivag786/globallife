<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Repositories\LeadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(StoreLeadRequest $request, LeadRepository $leads): RedirectResponse|JsonResponse
    {
        $data = $request->safe()->except('website');

        $leads->create($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Thanks! We'll be in touch shortly."]);
        }

        return back()->with('status', "Thanks for reaching out — we'll be in touch shortly.");
    }
}
