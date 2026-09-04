@extends('errors::minimal')

@section('icon')
    <flux:icon icon="key" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '401')
@section('title', __('Unauthorized'))
@section('message', __('Please log in to access this resource.'))
