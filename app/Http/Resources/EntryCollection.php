<?php

namespace App\Http\Resources;

use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EntryCollection extends ResourceCollection
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
                'subtitle' => $row->subtitle,
                'post_at' => $row->post_at,
                'is_publish' => (bool)$row->is_publish,
                'parents' => $this->fetchParents($row),
            ];
        })->toArray();
    }

    /**
     * 親記事取得
     *
     * @param EntryResource $entry
     * @return array
     */
    private function fetchParents(EntryResource $entry): array
    {
        $index = 0;
        $parents = [];
        $parent = $entry->parent;

        while ($parent) {
            $index -= 1;
            $parents[] = [
                'id' => $parent->id,
                'title' => $parent->title,
                'depth' => $index,
            ];
            $parent = $parent->parent;
        }

        return $parents;
    }
}
