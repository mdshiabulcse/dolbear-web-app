<?php

namespace App\Http\Resources\SiteResource;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CampaignPaginateResource extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                // Get title from currentLanguage relationship or fallback
                $title = null;
                $description = null;
                if ($data->currentLanguage && $data->currentLanguage->first()) {
                    $title = $data->currentLanguage->first()->title;
                    $description = $data->currentLanguage->first()->description;
                }

                return [
                    'id'                    => $data->id,
                    'slug'                  => $data->slug,
                    'title'                 => $title ?: ($data->event_title ?? $data->title),
                    'short_description'     => $description ?: nullCheck($data->short_description ?? $data->description),
                    'event_title'           => $data->event_title ?? $data->title,
                    'description'           => $data->description ?? $data->short_description,
                    'event_schedule_start'  => nullCheck($data->event_schedule_start ?? $data->campaign_start_date),
                    'event_schedule_end'    => nullCheck($data->event_schedule_end ?? $data->campaign_end_date),
                    'image_374x374'         => $data->image_374x374,
                    'image_1920x412'        => $data->image_1920x412,
                    'image_406x235'         => $data->image_406x235,
                    'banner'                => $data->banner_image_original ?? $data->image_1920x412,
                ];
            }),

            'total' => $this->total(),
            'count' => $this->count(),
            'per_page' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'total_pages' => $this->lastPage(),
            'last_page' => $this->lastPage(),
            'next_page_url' => $this->nextPageUrl(),
            'has_more_data' => $this->hasMorePages(),

        ];
    }
}
