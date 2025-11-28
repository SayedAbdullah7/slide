<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvestmentOpportunityResource;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\InvestmentOpportunity;
use App\Models\InvestmentOpportunityReminder;
use App\Services\InvestmentOpportunityReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentOpportunityReminderController extends Controller
{
    use ApiResponseTrait;

    protected $reminderService;

    public function __construct(InvestmentOpportunityReminderService $reminderService)
    {
        $this->reminderService = $reminderService;
    }

    /**
     * Get all reminders for the authenticated investor
     * الحصول على جميع التذكيرات للمستثمر المصادق عليه
     */
    public function index(Request $request)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر');
        }

        $perPage = $request->query('per_page', 15);
        $status = $request->query('status', 'all'); // all, active, sent

        $query = $investor->reminders()->with(['investmentOpportunity.category', 'investmentOpportunity.ownerProfile']);

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'sent') {
            $query->whereNotNull('reminder_sent_at');
        }

        $reminders = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->respondSuccessWithData('تم جلب التذكيرات بنجاح', $reminders);
    }

    /**
     * Add a reminder for a coming investment opportunity
     * إضافة تذكير لفرصة استثمارية قادمة
     */
    public function store(Request $request)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر');
        }

        $request->validate([
            'investment_opportunity_id' => 'required|exists:investment_opportunities,id',
        ]);

        try {
            $opportunity = InvestmentOpportunity::findOrFail($request->investment_opportunity_id);

            // Check if opportunity is coming (not yet available for investment)
            if (!$opportunity->isComing()) {
                return $this->respondBadRequest('لا يمكن إضافة تذكير لهذه الفرصة. يجب أن تكون الفرصة قادمة وليست متاحة للاستثمار بعد');
            }

            // Check if reminder already exists
            $existingReminder = $investor->reminders()
                ->where('investment_opportunity_id', $opportunity->id)
                ->first();

            if ($existingReminder) {
                if ($existingReminder->is_active) {
                    return $this->respondBadRequest('تم إضافة تذكير لهذه الفرصة مسبقاً');
                } else {
                    // Reactivate existing reminder
                    $existingReminder->update(['is_active' => true, 'reminder_sent_at' => null]);
                    return $this->respondSuccessWithData('تم تفعيل التذكير بنجاح', $existingReminder);
                }
            }

            // Create new reminder
            $reminder = $investor->reminders()->create([
                'investment_opportunity_id' => $opportunity->id,
                'is_active' => true,
            ]);

            $reminder->load(['investmentOpportunity.category', 'investmentOpportunity.ownerProfile']);

            return $this->respondSuccess('تم إضافة التذكير بنجاح');

        } catch (\Exception $e) {
            return $this->respondBadRequest('حدث خطأ أثناء إضافة التذكير: ' . $e->getMessage());
        }
    }

    /**
     * Remove a reminder
     * إزالة تذكير
     */
    public function destroy($reminderId)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر');
        }

        $reminder = $investor->reminders()->findOrFail($reminderId);

        $reminder->update(['is_active' => false]);

        return $this->respondSuccess('تم إزالة التذكير بنجاح');
    }

    /**
     * Toggle reminder status (activate/deactivate)
     * تفعيل أو إلغاء تفعيل التذكير
     */
    public function toggle(Request $request, $reminderId)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر');
        }

        $reminder = $investor->reminders()->findOrFail($reminderId);
        $isActive = $request->boolean('is_active', !$reminder->is_active);

        $reminder->update(['is_active' => $isActive]);

        $message = $isActive ? 'تم تفعيل التذكير بنجاح' : 'تم إلغاء تفعيل التذكير بنجاح';

        return $this->respondSuccessWithData($message, $reminder);
    }

    /**
     * Get coming opportunities that can have reminders
     * الحصول على الفرص القادمة التي يمكن إضافة تذكيرات لها
     */
    public function comingOpportunities(Request $request)
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر');
        }

        $perPage = $request->query('per_page', 15);

        // Get coming opportunities
        $comingOpportunities = InvestmentOpportunity::coming()
            ->with(['category', 'ownerProfile'])
            ->orderBy('offering_start_date', 'asc')
            ->paginate($perPage);

        // Get existing reminders for these opportunities
        $existingReminderIds = $investor->reminders()
            ->whereIn('investment_opportunity_id', $comingOpportunities->pluck('id'))
            ->where('is_active', true)
            ->pluck('investment_opportunity_id')
            ->toArray();

        // Add reminder status to each opportunity
        $comingOpportunities->getCollection()->transform(function ($opportunity) use ($existingReminderIds) {
            $opportunity->has_reminder = in_array($opportunity->id, $existingReminderIds);
            return $opportunity;
        });

        return $this->respondSuccessWithData('تم جلب الفرص القادمة بنجاح', $comingOpportunities);
    }

    /**
     * Get reminder statistics
     * الحصول على إحصائيات التذكيرات
     */
    public function stats()
    {
        $investor = Auth::user()?->investorProfile;
        if (!$investor) {
            return $this->respondForbidden('لم يتم العثور على بروفايل المستثمر');
        }

        $stats = [
            'total_reminders' => $investor->reminders()->count(),
            'active_reminders' => $investor->reminders()->active()->count(),
            'sent_reminders' => $investor->reminders()->whereNotNull('reminder_sent_at')->count(),
            'pending_reminders' => $investor->reminders()->active()->whereNull('reminder_sent_at')->count(),
        ];

        return $this->respondSuccessWithData('تم جلب إحصائيات التذكيرات بنجاح', $stats);
    }
}
