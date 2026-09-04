@props([
    'maxWidth' => '5xl',
    'gap' => '6',
])

@php
    $maxWidthClasses = [
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        '6xl' => 'max-w-6xl',
        '7xl' => 'max-w-7xl',
    ];

    $widthClass = $maxWidthClasses[$maxWidth] ?? 'max-w-5xl';
@endphp

<section {{ $attributes->merge([
    'class' => "mx-auto flex w-full {$widthClass} flex-col gap-{$gap}",
]) }}>
    {{ $slot }}
</section>
