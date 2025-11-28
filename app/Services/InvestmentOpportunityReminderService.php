<?php

namespace App\Services;

use App\Models\InvestmentOpportunity;
use App\Models\InvestmentOpportunityReminder;
use App\Models\InvestorProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class InvestmentOpportunityReminderService
{
    /**
     * Add a reminder for an investment opportunity
     */
    public function addReminder(InvestorProfile $investor, InvestmentOpportunity $opportunity): InvestmentOpportunityReminder
    {
        // Check if opportunity is coming
        if (!$opportunity->isComing()) {
            throw new \InvalidArgumentException('Cannot add reminder for opportunity that is not coming');
        }

        // Check if reminder already exists
        $existingReminder = $investor->reminders()
            ->where('investment_opportunity_id', $opportunity->id)
            ->first();

        if ($existingReminder) {
            if ($existingReminder->is_active) {
                throw new \InvalidArgumentException('Reminder already exists for this opportunity');
            } else {
                // Reactivate existing reminder
                $existingReminder->update([
                    'is_active' => true,
                    'reminder_sent_at' => null
                ]);
                return $existingReminder;
            }
        }

        // Create new reminder
        return $investor->reminders()->create([
            'investment_opportunity_id' => $opportunity->id,
            'is_active' => true,
        ]);
    }

    /**
     * Remove a reminder
     */
    public function removeReminder(InvestorProfile $investor, int $reminderId): bool
    {
        $reminder = $investor->reminders()->findOrFail($reminderId);
        return $reminder->update(['is_active' => false]);
    }

    /**
     * Toggle reminder status
     */
    public function toggleReminder(InvestorProfile $investor, int $reminderId, bool $isActive): bool
    {
        $reminder = $investor->reminders()->findOrFail($reminderId);
        return $reminder->update(['is_active' => $isActive]);
    }

    /**
     * Get reminders that should be sent (opportunities that became available)
     */
    public function getRemindersToSend(): Collection
    {
        return InvestmentOpportunityReminder::with(['investorProfile.user', 'investmentOpportunity'])
            ->active()
            ->unsent()
            ->whereHas('investmentOpportunity', function ($query) {
                $query->where('status', 'open')
                    ->where('offering_start_date', '<=', now())
                    ->where('show', true);
            })
            ->get();
    }

    /**
     * Send reminder notifications
     */
    public function sendReminders(): int
    {
        $reminders = $this->getRemindersToSend();
        $sentCount = 0;

        foreach ($reminders as $reminder) {
            try {
                $this->sendReminderNotification($reminder);
                $reminder->markAsSent();
                $sentCount++;

                Log::info('Reminder sent successfully', [
                    'reminder_id' => $reminder->id,
                    'investor_id' => $reminder->investor_profile_id,
                    'opportunity_id' => $reminder->investment_opportunity_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send reminder', [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sentCount;
    }

    /**
     * Send individual reminder notification
     */
    protected function sendReminderNotification(InvestmentOpportunityReminder $reminder): void
    {
        $opportunity = $reminder->investmentOpportunity;
        $investor = $reminder->investorProfile;
        $user = $investor->user;

        // Here you can implement the actual notification logic
        // For now, we'll just log it
        Log::info('Sending reminder notification', [
            'user_id' => $user->id,
            'user_phone' => $user->phone,
            'opportunity_name' => $opportunity->name,
            'opportunity_start_date' => $opportunity->offering_start_date,
        ]);

        // TODO: Implement actual notification sending (SMS, Email, Push, etc.)
        // Example:
        // - Send SMS notification
        // - Send email notification
        // - Send push notification
        // - Add to notification queue
    }

    /**
     * Get reminder statistics for an investor
     */
    public function getReminderStats(InvestorProfile $investor): array
    {
        return [
            'total_reminders' => $investor->reminders()->count(),
            'active_reminders' => $investor->reminders()->active()->count(),
            'sent_reminders' => $investor->reminders()->whereNotNull('reminder_sent_at')->count(),
            'pending_reminders' => $investor->reminders()->active()->whereNull('reminder_sent_at')->count(),
        ];
    }

    /**
     * Clean up old sent reminders (optional)
     */
    public function cleanupOldReminders(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return InvestmentOpportunityReminder::whereNotNull('reminder_sent_at')
            ->where('reminder_sent_at', '<', $cutoffDate)
            ->where('is_active', false)
            ->delete();
    }

    /**
     * Get coming opportunities with reminder status for an investor
     */
    public function getComingOpportunitiesWithReminderStatus(InvestorProfile $investor, int $perPage = 15)
    {
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

        return $comingOpportunities;
    }
}
