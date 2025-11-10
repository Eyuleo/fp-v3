<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isClient() && $this->user()->hasVerifiedEmail();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id'        => ['required', 'exists:services,id'],
            'requirements'      => ['required', 'string', 'min:10', 'max:5000'],
            'requirements_file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,doc,docx,txt,jpg,jpeg,png,zip'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'requirements.required'   => 'Please provide detailed requirements for your order.',
            'requirements.min'        => 'Requirements must be at least 10 characters.',
            'requirements.max'        => 'Requirements cannot exceed 5000 characters.',
            'requirements_file.max'   => 'File size cannot exceed 25MB.',
            'requirements_file.mimes' => 'File must be a PDF, DOC, DOCX, TXT, JPG, PNG, or ZIP file.',
        ];
    }
}
