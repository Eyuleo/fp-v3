<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'bio'        => ['nullable', 'string', 'max:1000'],
            'avatar'     => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:10240'], // 10MB
        ];

        // Additional rules for students
        if ($user->isStudent()) {
            $rules['university']  = ['nullable', 'string', 'max:255'];
            $rules['portfolio']   = ['nullable', 'array', 'max:5'];
            $rules['portfolio.*'] = ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240']; // 10MB per file
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($user->isStudent() && $this->hasFile('portfolio')) {
                $existingCount = count($user->portfolio_paths ?? []);
                $newCount      = count($this->file('portfolio'));
                $totalCount    = $existingCount + $newCount;

                if ($totalCount > 5) {
                    $validator->errors()->add('portfolio', "You can only have up to 5 portfolio files. You currently have {$existingCount}.");
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'avatar.max'        => 'The avatar must not be larger than 10MB.',
            'portfolio.*.max'   => 'Each portfolio file must not be larger than 10MB.',
            'portfolio.*.mimes' => 'Portfolio files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
        ];
    }
}
