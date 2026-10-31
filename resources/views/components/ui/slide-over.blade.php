{{-- <x-ui.slide-over name="…" title="…"> → partials/modal/slide-over (right-hand panel, 50% black backdrop).
     Open with sharet.panel('{name}', payload). x-text on the component sets a live title. --}}
@props(['name', 'title' => '', 'subtitle' => null, 'width' => 'max-w-xl'])
@include('partials.modal.slide-over', ['name' => $name, 'title' => $title, 'subtitle' => $subtitle, 'width' => $width,
    'titleExpr' => $attributes->get('x-text'), 'slot' => $slot, 'footer' => $footer ?? null])
