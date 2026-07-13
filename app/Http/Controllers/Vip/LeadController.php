<?php

namespace App\Http\Controllers\Vip;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(): View
    {
        $micrositeId = Auth::user()->vipMicrosite->id;
        $leads = Lead::where('vip_microsite_id', $micrositeId)->latest()->paginate(20);

        return view('vip.leads.index', ['leads' => $leads]);
    }

    public function export(): StreamedResponse
    {
        $micrositeId = Auth::user()->vipMicrosite->id;
        $leads = Lead::where('vip_microsite_id', $micrositeId)->latest()->get();

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'City', 'Message', 'Status', 'Date']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->name, $lead->email, $lead->phone, $lead->city,
                    $lead->message, $lead->status, $lead->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, 'leads.csv', ['Content-Type' => 'text/csv']);
    }
}
