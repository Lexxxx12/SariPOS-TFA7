<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($this->route('user'))],
            'full_name' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Please enter a username.', 'username.unique' => 'This username is already taken. Please choose another.',
            'full_name.required' => 'Please enter the user’s full name.', 'avatar.image' => 'Please choose a valid image.',
            'avatar.mimes' => 'Choose a JPG, PNG, or WebP image.', 'avatar.max' => 'The profile picture must be no larger than 2 MB.',
            'avatar.dimensions' => 'The profile picture must be at most 4096 × 4096 pixels.',
        ];
    }
}
