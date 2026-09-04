@extends('errors::minimal')

@section('icon')
    <flux:icon icon="clock" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '419')
@section('title', __('Page Expired'))
@section('message', __('Your session has expired. Please try again.'))
