<?php
namespace App\Common;

use App\Models\Post;
use \Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MyPaginator extends LengthAwarePaginator
{
    public function toArray()
    {
        return [
            'currentPage' => $this->currentPage(),
            'data' => $this->items->toArray(),
            // 'first_page_url' => $this->url(1),
            // 'from' => $this->firstItem(),
            'lastPage' => $this->lastPage(),
            // 'last_page_url' => $this->url($this->lastPage()),
            // 'next_page_url' => $this->nextPageUrl(),
            // 'path' => $this->path,
            // 'perPage' => $this->perPage(),
            // 'prev_page_url' => $this->previousPageUrl(),
            // 'to' => $this->lastItem(),
            // 'total' => $this->total(),
        ];
    }

    public function appendPost0(Post $post)
    {
        $this->items->prepend($post);
    }
}