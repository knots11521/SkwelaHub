@extends('errors::minimal')

@section('icon')
    <flux:icon icon="magnifying-glass" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '404')
@section('title', __('Not Found'))
@section('message', __('The page you are looking for could not be found. It may have been moved or deleted.'))
