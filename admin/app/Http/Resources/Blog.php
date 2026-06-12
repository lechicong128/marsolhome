<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Blog extends JsonResource
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
            'image' => !empty($this->image) ? asset('storage/' . $this->image) : null,
            'title' => $this->title,
            'descption' => $this->descption,
            'content' => $this->content,
            'active' => $this->active,
            'type' => $this->type,
            'type_blog' => $this->type_blog,
            'category_name' => $this->blogCategory ? $this->blogCategory->name : null,
            'hot' => $this->hot,
            'homepage' => $this->homepage,
            'staff_create_name' => $this->creator ? $this->creator->name : null,
            'view' => $this->view ?? 0,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    public function with($request)
    {
        return [
            "result" => true
        ];
    }
}
