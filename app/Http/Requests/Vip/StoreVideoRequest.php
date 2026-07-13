<?php

namespace App\Http\Requests\Vip;

use App\Models\BusinessVideo;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('vip_member');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'youtube_url' => ['required', 'url', 'max:255'],
            'title' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! BusinessVideo::extractYoutubeId((string) $this->input('youtube_url'))) {
                $validator->errors()->add('youtube_url', 'That does not look like a valid YouTube URL.');
            }

            if ($this->user()->vipMicrosite->videos()->count() >= 6) {
                $validator->errors()->add('youtube_url', 'You can add up to 6 videos only.');
            }
        });
    }
}
