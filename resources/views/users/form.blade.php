@extends('layouts.app')
@section('content')
<section class="mx-auto max-w-3xl px-5 py-8 sm:px-8 lg:py-12">
 <a class="text-sm font-bold text-teal-700 hover:underline" href="{{ route('users.index') }}">← Back to users</a>
 <div class="my-7"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-teal-700">Account management</p><h1 class="mt-2 text-3xl font-black tracking-tight">{{ $user->exists ? 'Edit User' : 'Create User' }}</h1><p class="mt-3 text-sm text-slate-500">Manage the people in your store workspace.</p></div>
 <form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-900/5 sm:p-9">
 @csrf @if($user->exists) @method('PUT') @endif
 @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>Please check the highlighted fields.</strong><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <p class="text-xs text-slate-500">Fields marked * are required.</p>
 <x-form-field name="username" label="Username" type="text" :value="$user->username" :required="true" :maxlength="50" />
<x-form-field name="full_name" label="Full name" type="text" :value="$user->full_name" :required="true" :maxlength="100" />
@if($user->exists)<div class="rounded-2xl border border-slate-200 bg-slate-50 p-5"><label for="avatar" class="block text-sm font-bold">Profile picture</label><div class="my-4 flex items-center gap-4"><img class="size-20 rounded-2xl object-cover" src="{{ $user->avatar ? Storage::disk('public')->url($user->avatar) : asset('images/avatar-placeholder.svg') }}" alt="Current profile picture" width="80" height="80"><p class="text-xs leading-6 text-slate-500">JPG, PNG, or WebP · Maximum 2 MB<br>Prepared as a 256 × 256 thumbnail.<br>Leave empty to keep the current picture.</p></div><input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="avatar-help @error('avatar') avatar-error @enderror" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"> <p id="avatar-help" class="mt-2 text-xs text-slate-500">Maximum dimensions: 4096 × 4096 pixels. After a validation error, please select your file again.</p>@error('avatar')<p id="avatar-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror</div>@endif
 <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6"><button type="submit" class="rounded-xl bg-navy px-6 py-3 text-sm font-bold text-white hover:bg-coral">{{ $user->exists ? 'Save changes' : 'Create user' }}</button><a class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600" href="{{ route('users.index') }}">Cancel</a></div>
 </form>
</section>
@endsection
