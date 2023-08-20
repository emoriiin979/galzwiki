<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BriefCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($row) {
            return [
                'id' => (int)$row->id,
                'title' => $row->title,
                'note' => $row->note,
                'entry_at' => $row->entry_at,
                'is_publish' => (bool)$row->is_publish,
                'parents' => $row->parents
                    ? array_map(function ($parent, $index) {
                        return [
                            'id' => (int)$parent->id,
                            'title' => $parent->title,
                            'depth' => $index * (-1),
                        ];
                    }, $row->parents, range(1, count($row->parents)))
                    : [],
            ];
        })->toArray();
    }
}
