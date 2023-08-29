<?php

namespace App\Http\Resources;

use App\Models\Brief;
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
                'parents' => $this->fetchParents($row),
            ];
        })->toArray();
    }

    /**
     * 親記事取得
     *
     * @param BriefResource $brief
     * @return array
     */
    private function fetchParents(BriefResource $brief): array
    {
        $index = 0;
        $parents = [];
        $parent = $brief->parent;

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
