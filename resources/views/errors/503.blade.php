@extends('errors::minimal')

@section('icon')
    <flux:icon icon="server-stack" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '503')
@section('title', __('Service Unavailable'))
@section('message', __('We\'re temporarily unavailable. Please try again shortly.'))
