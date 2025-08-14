<?php

namespace Marshmallow\AdvancedNovaMediaLibrary\Http\Requests;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'search_text' => 'sometimes|nullable|string',
            'page' => 'sometimes|nullable|numeric',
            'per_page' => 'sometimes|nullable|numeric'
        ];
    }

    public function getCustomProperties()
    {
        $keys = ['custom_properties'];
        foreach ($this->all() as $key => $value) {
            if (Str::startsWith($key, 'custom_properties->')) {
                $keys[] = $key;
            }
        }

        $data = $this->only($keys);
        $customProperties = Arr::get($data, 'custom_properties', []);

        // Also check for direct custom property keys that might be passed
        foreach ($this->all() as $key => $value) {
            if (!in_array($key, ['_mediaId', '_mediaData', '_mediaProperties', '_token', 'search_text', 'page', 'per_page'])) {
                $customProperties[$key] = $value;
            }
        }

        return $customProperties;
    }
}
