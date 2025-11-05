@extends('admin.layout')

@section('admin-title')
    $guild->id ? Edit Guild
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Guilds' => 'admin/guilds', ($guild->id ? 'Edit' : 'Create') . ' Guild' => $guild->id ? 'admin/guilds/edit/' . $guild->id : 'admin/guilds/create']) !!}

    <h3>Basic Information</h3>
    
    <p>Form content here</p>


@endsection
