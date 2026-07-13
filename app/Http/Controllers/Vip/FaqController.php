<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vip\StoreFaqRequest;
use App\Http\Requests\Vip\UpdateFaqRequest;
use App\Models\BusinessFaq;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class FaqController extends Controller
{
    public function index(): View
    {
        $faqs = Auth::user()->vipMicrosite->faqs()->get();

        return view('vip.faqs.index', ['faqs' => $faqs]);
    }

    public function store(StoreFaqRequest $request): RedirectResponse
    {
        BusinessFaq::create([
            'vip_microsite_id' => Auth::user()->vipMicrosite->id,
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return redirect()->route('vip.faqs.index')->with('status', 'FAQ added.');
    }

    public function update(UpdateFaqRequest $request, BusinessFaq $faq): RedirectResponse
    {
        $faq->update([
            'question' => $request->input('question'),
            'answer' => $request->input('answer'),
            'sort_order' => $request->input('sort_order', 0),
            'is_visible' => $request->boolean('is_visible'),
        ]);

        return redirect()->route('vip.faqs.index')->with('status', 'FAQ updated.');
    }

    public function destroy(BusinessFaq $faq): RedirectResponse
    {
        abort_unless($faq->vip_microsite_id === Auth::user()->vipMicrosite->id, 403);

        $faq->delete();

        return redirect()->route('vip.faqs.index')->with('status', 'FAQ deleted.');
    }
}
