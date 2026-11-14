@extends('layouts.app')
@section('content')
<section class="mx-auto max-w-3xl px-5 py-8 sm:px-8 lg:py-12">
 <a class="text-sm font-bold text-teal-700 hover:underline" href="{{ route('customers.index') }}">← Back to customers</a>
 <div class="my-7"><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-teal-700">Account management</p><h1 class="mt-2 text-3xl font-black tracking-tight">{{ $customer->exists ? 'Edit Customer' : 'Create Customer' }}</h1><p class="mt-3 text-sm text-slate-500">Keep customer contact details accurate and up to date.</p></div>
 <form action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-900/5 sm:p-9">
 @csrf @if($customer->exists) @method('PUT') @endif
 @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800"><strong>Please check the highlighted fields.</strong><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
 <p class="text-xs text-slate-500">Fields marked * are required.</p>
 <x-form-field name="full_name" label="Full name" type="text" :value="$customer->full_name" :required="true" :maxlength="100" />
<x-form-field name="email" label="Email address" type="email" :value="$customer->email" :required="true" :maxlength="100" />
<x-form-field name="phone" label="Phone number" type="text" :value="$customer->phone" :required="false" :maxlength="20" />

 <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6"><button type="submit" class="rounded-xl bg-navy px-6 py-3 text-sm font-bold text-white hover:bg-coral">{{ $customer->exists ? 'Save changes' : 'Create customer' }}</button><a class="rounded-xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-600" href="{{ route('customers.index') }}">Cancel</a></div>
 </form>
</section>
@endsection
