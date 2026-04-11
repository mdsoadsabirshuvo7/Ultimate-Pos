<?php

namespace Modules\Accounting\Http\Controllers;

use Modules\Accounting\Entities\BusinessLocation;
use Modules\Accounting\Entities\Currency;
use Modules\Accounting\Services\ApiService;
use Modules\Accounting\Entities\PaymentDetail;
use App\Utils\Util;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Accounting\Exports\AccountingExport;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Entities\ChartOfAccount;
use Modules\Accounting\Entities\JournalEntry;
use Modules\Accounting\Entities\PaymentType;
use Modules\Accounting\Entities\Transfer;
use Yajra\DataTables\Facades\DataTables;

class AccountingController extends Controller
{
    private $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    public function trial_balance(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $location_id = $request->location_id;
        $data = [];
        $business_locations = BusinessLocation::getDropdownCollection(session('business.id'));
        if (!empty($start_date)) {
            $data = DB::table("chart_of_accounts")->join("journal_entries", "journal_entries.chart_of_account_id", "chart_of_accounts.id")->join("business_locations", "journal_entries.location_id", "business_locations.id")->when($start_date, function ($query) use ($start_date, $end_date) {
                $query->whereBetween('journal_entries.date', [$start_date, $end_date]);
            })->when($location_id, function ($query) use ($location_id) {
                $query->where('journal_entries.location_id', $location_id);
            })->where('chart_of_accounts.active', 1)->selectRaw("chart_of_accounts.name,chart_of_accounts.gl_code,chart_of_accounts.account_type,business_locations.name business_location,SUM(journal_entries.debit) debit,SUM(journal_entries.credit) credit")->groupBy("chart_of_accounts.id")->get();
            if ($request->download) {
                if ($request->type == 'pdf') {
                    $pdf = PDF::loadView('accounting::report.trial_balance_pdf', compact(
                        'start_date',
                        'end_date',
                        'location_id',
                        'data',
                        'business_locations'
                    ));
                    return $pdf->download(trans_choice('accounting::general.trial_balance', 1) . '(' . $start_date . ' to ' . $end_date . ').pdf');
                }
                $view = view(
                    'accounting::report.trial_balance_pdf',
                    compact(
                        'start_date',
                        'end_date',
                        'location_id',
                        'data',
                        'business_locations'
                    )
                );
                if ($request->type == 'excel_2007') {
                    return Excel::download(new AccountingExport($view), trans_choice('accounting::general.trial_balance', 1) . '(' . $start_date . ' to ' . $end_date . ').xlsx');
                }
                if ($request->type == 'excel') {
                    return Excel::download(new AccountingExport($view), trans_choice('accounting::general.trial_balance', 1) . '(' . $start_date . ' to ' . $end_date . ').xls');
                }
                if ($request->type == 'csv') {
                    return Excel::download(new AccountingExport($view), trans_choice('accounting::general.trial_balance', 1) . '(' . $start_date . ' to ' . $end_date . ').csv');
                }
            }
        }
        return view(
            'accounting::report.trial_balance',
            compact(
                'start_date',
                'end_date',
                'location_id',
                'data',
                'business_locations',
            )
        );
    }

    public function AccountsDropdown()
    {
        return [];
    }
}
