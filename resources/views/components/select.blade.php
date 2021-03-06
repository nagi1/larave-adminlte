@extends('adminlte::components.input-group-component')

@section('input_group_item')

    {{-- Select --}}
    <select id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->merge(['class' => $makeItemClass($errors->first($name))]) }}>
        {{ $slot }}
    </select>

@overwrite