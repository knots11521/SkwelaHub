@extends('errors::minimal')

@section('icon')
    <flux:icon icon="bolt" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '429')
@section('title', __('Too Many Requests'))
@section('message', __('Too many requests. Please slow down and try again.'))
