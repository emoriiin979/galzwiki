<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'body' => $this->body,
            'parent_entry_id' => $this->parent_entry_id,
            'post_user_id' => $this->post_user_id,
            'is_publish' => $this->is_publish,
            'updated_at' => $this->updated_at,
            'parents' => $this->fetchParents($this),
        ];
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
