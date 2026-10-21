<?php

namespace App\Http\Controllers;

use App\Enums\MembershipStatus;
use App\Models\Member;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function overdueDues(Request $request)
    {
        $term = $request->query('term', '2026-2027');

        $unpaid = Member::query()
            ->where('is_verified', true)
            ->where('membership_status', MembershipStatus::Active)
            ->whereDoesntHave('payments', fn ($q) => $q->completedDues()->where('academic_term', $term))
            ->orderBy('student_number')
            ->paginate(50);

        return response()->json($unpaid);
    }
}

