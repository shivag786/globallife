<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Repositories\LeadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class EnquiryController extends Controller
{
    public function store(StoreLeadRequest $request, LeadRepository $leads): RedirectResponse|JsonResponse
    {
        // Only real lead fields are stored; the honeypot and the form's own
        // bookkeeping (which form, where to return) are not part of the lead.
        $data = $request->safe()->except(['website', 'enquiry_context', 'return_to']);

        // A product id only belongs on a product enquiry.
        if (($data['source'] ?? null) !== 'product') {
            unset($data['product_id']);
        }

        $leads->create($data);

        if ($request->wantsJson()) {
            return response()->json(['message' => "Thanks! We'll be in touch shortly."]);
        }

        $status = "Thanks for reaching out — we'll be in touch shortly.";

        // Back to the page the form was on, not wherever the session last was.
        $return = $request->returnUrl();

        return $return
            ? redirect()->to($return)->with('status', $status)
            : back()->with('status', $status);
    }
}
