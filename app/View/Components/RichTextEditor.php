<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RichTextEditor extends Component
{
    public function __construct(
        public string $id,
        public string $placeholder = '',
        public string $minHeight = 'min-h-40',
        public bool $focusOnComment = false,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.rich-text-editor');
    }
}
