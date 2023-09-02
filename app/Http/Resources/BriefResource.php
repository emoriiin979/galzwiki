<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BriefResource extends JsonResource
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
            'note' => $this->note,
            'abstract' => $this->abstract,
            'entry_user_id' => $this->entry_user_id,
            'entry_at' => $this->entry_at,
            'is_publish' => $this->is_publish,
            'parents' => $this->fetchParents($this),
        ];
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
