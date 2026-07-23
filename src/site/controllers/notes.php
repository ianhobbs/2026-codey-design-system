<?php
/** Project STARTER controller — feeds the notes listing template. */
return function ($page) {
    $tag   = param('tag');
    $notes = $page->children()->listed()->sortBy('date', 'desc');
    if ($tag) {
        $notes = $notes->filterBy('tags', $tag, ',');
    }
    return [
        'notes' => $notes->paginate(12),
        'tag'   => $tag,
        'tags'  => $page->children()->listed()->pluck('tags', ',', true),
    ];
};
