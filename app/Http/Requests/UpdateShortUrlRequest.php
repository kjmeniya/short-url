<?php

namespace App\Http\Requests;

use App\Rules\SlugAvailable;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validated request for updating a short URL.
 * Shared by User\MyLinkController and Admin\ShortUrlController.
 *
 * The SlugAvailable rule ignores the current record's ID on uniqueness check.
 * The route parameter name differs between user (/links/{id}) and admin (/short-urls/{short_url}).
 */
class UpdateShortUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by route middleware
    }

    /**
     * Auto-lowercase the slug before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('custom_alias')) {
            $this->merge([
                'custom_alias' => strtolower(trim($this->input('custom_alias'))),
            ]);
        }
    }

    public function rules(): array
    {
        // Route param name differs: user uses {id}, admin uses {short_url}
        $id = $this->route('id') ?? $this->route('short_url');

        return [
            'original_url' => ['required', 'url', 'max:2048'],
            'mobile_url'   => ['nullable', 'url', 'max:2048'],
            'desktop_url'  => ['nullable', 'url', 'max:2048'],
            'tablet_url'   => ['nullable', 'url', 'max:2048'],
            'title'        => ['nullable', 'string', 'max:255'],
            'custom_alias' => [
                'nullable',
                'string',
                'min:3',
                'max:64',
                'regex:/^[a-z0-9\-_]+$/',
                new SlugAvailable((int) $id),
            ],
            'expires_at'   => ['nullable', 'date'],
            'max_clicks'   => ['nullable', 'integer', 'min:1'],
            'status'       => ['required', 'in:active,inactive,expired'],
            'password'     => ['nullable', 'string', 'min:4', 'max:100', 'confirmed'],
            'clear_password' => ['nullable', 'boolean'],
            'is_private'   => ['nullable', 'boolean'],
            'is_24h_story' => ['nullable', 'boolean'],
            'is_one_time'  => ['nullable', 'boolean'],
            'timezone'     => ['nullable', 'string', 'timezone'],
            'office_days'  => ['nullable', 'array'],
            'office_days.*'=> ['string'],
            'office_start_time' => ['nullable', 'date_format:H:i'],
            'office_end_time'   => ['nullable', 'date_format:H:i', 'after:office_start_time'],
            'office_url'      => ['nullable', 'url', 'max:2048'],
            'after_hours_url' => ['nullable', 'url', 'max:2048'],
            'og_title'        => ['nullable', 'string', 'max:255'],
            'og_description'  => ['nullable', 'string'],
            'og_image'        => ['nullable', 'url', 'max:2048'],
            'ip_blocks'       => ['nullable', 'array'],
            'ip_blocks.*.id'  => ['nullable', 'integer', 'exists:ip_blocks,id'],
            'ip_blocks.*.type'=> ['required', 'in:ip,cidr'],
            'ip_blocks.*.value'=> ['required', 'string'],
            'redirect_delay'  => ['nullable', 'integer', 'min:0', 'max:' . \App\Models\Setting::get('max_redirect_delay', 30)],
        ];
    }

    public function messages(): array
    {
        return [
            'original_url.required' => 'The destination URL is required.',
            'original_url.url'      => 'Please enter a valid URL (including https://).',
            'custom_alias.min'      => 'The slug must be at least 3 characters.',
            'custom_alias.max'      => 'The slug may not exceed 64 characters.',
            'custom_alias.regex'    => 'Slug may only contain lowercase letters, numbers, hyphens, and underscores.',
            'max_clicks.integer'    => 'The click limit must be a whole number.',
            'max_clicks.min'        => 'The click limit must be at least 1.',
        ];
    }
}

