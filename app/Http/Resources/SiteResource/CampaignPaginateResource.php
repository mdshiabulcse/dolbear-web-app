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
                    'title'                 => $title ?: $data->title,
                    'short_description'     => $description ?: nullCheck($data->short_description),
                    'campaign_start_date'   => nullCheck($data->campaign_start_date),
                    'campaign_end_date'     => nullCheck($data->campaign_end_date),
                    'image_374x374'         => $data->image_374x374,
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
