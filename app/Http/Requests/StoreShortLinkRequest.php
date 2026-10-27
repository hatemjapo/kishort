<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShortLinkRequest extends FormRequest
{
    protected const FIELD_TARGET_URL = 'target_url';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            self::FIELD_TARGET_URL => [
                'required',
                'url',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $scheme = parse_url($value, PHP_URL_SCHEME);

                    if (!in_array($scheme, ['http', 'https'], true)) {
                        fail('Only http or https urls are allowed');
                    }
                },
            ],
        ];
    }
}
