@extends('errors::minimal')

@section('icon')
    <flux:icon icon="bug" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '500')
@section('title', __('Internal Server Error'))
@section('message', __('Something went wrong on our end. Please try again later.'))
