@extends('errors::minimal')

@section('icon')
    <flux:icon icon="lock-closed" class="w-12 h-12 text-teal-600 dark:text-teal-400" />
@endsection

@section('code', '403')
@section('title', __('Forbidden'))
@section('message', __('You do not have permission to access this resource.'))
