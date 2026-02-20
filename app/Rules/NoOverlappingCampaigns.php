<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Event;
use Carbon\Carbon;

class NoOverlappingCampaigns implements Rule
{
    protected $eventId;
    protected $startDate;
    protected $endDate;
    protected $eventType;

    public function __construct($eventId = null, $startDate, $endDate, $eventType = 'date_range')
    {
        $this->eventId = $eventId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->eventType = $eventType;
    }

    public function passes($attribute, $value)
    {
        // Only validate for active status and date_range type
        if ($value !== 'active' || $this->eventType !== 'date_range') {
            return true;
        }

        // Need valid dates to check overlap
        if (!$this->startDate || !$this->endDate) {
            return true;
        }

        $query = Event::where('status', 'active')
            ->where('is_active', 1)
            ->where('event_type', 'date_range')
            ->where('id', '!=', $this->eventId)
            ->where(function ($q) {
                // Check for date range overlap
                $q->where(function ($query) {
                        // Campaign A starts before Campaign B ends
                        // AND Campaign A ends after Campaign B starts
                        $query->where('event_schedule_start', '<=', $this->endDate)
                              ->where('event_schedule_end', '>=', $this->startDate);
                    });
            });

        return !$query->exists();
    }

    public function message()
    {
        return __('Campaign dates overlap with an existing active campaign. Only one campaign can be active at a time.');
    }
}
