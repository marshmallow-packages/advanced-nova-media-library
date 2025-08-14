<?php

namespace Marshmallow\AdvancedNovaMediaLibrary\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Marshmallow\AdvancedNovaMediaLibrary\Http\Requests\MediaRequest;
use Marshmallow\AdvancedNovaMediaLibrary\Http\Resources\MediaResource;

class MediaController extends Controller
{
    public function index(MediaRequest $request)
    {
        if (!config('nova-media-library.enable-existing-media')) {
            throw new Exception('You need to enable the `existing media` feature via config.');
        }

        $hideCollections = config('nova-media-library.hide-media-collections', []);
        $mediaClass = config('media-library.media_model');
        $mediaClassIsSearchable = method_exists($mediaClass, 'search');

        $searchText = $request->input('search_text') ?: null;
        $perPage = $request->input('per_page') ?: 18;

        $query = null;

        if ($searchText && $mediaClassIsSearchable) {
            $query = $mediaClass::search($searchText);
        } else {
            $query = $mediaClass::query();

            if ($searchText) {
                $query->where(function ($query) use ($searchText) {
                    $query->where('name', 'LIKE', '%' . $searchText . '%');
                    $query->orWhere('file_name', 'LIKE', '%' . $searchText . '%');
                });
            }

            $query->latest();
        }

        if (!empty($hideCollections)) {
            if (!is_array($hideCollections)) {
                $hideCollections = [$hideCollections];
            }

            $query->whereNotIn('collection_name', $hideCollections);
        }

        $results = $query->paginate($perPage);

        return MediaResource::collection($results);
    }

    public function updateMediaItemProperties(MediaRequest $request, $mediaItemId)
    {
        $custom_properties = $request->input('_mediaProperties', []);

        // Fallback to direct properties if _mediaProperties is not set
        if (empty($custom_properties)) {
            $custom_properties = $request->except(['_mediaId', '_mediaData', '_token']);
        }

        $mediaClass = config('media-library.media_model');
        $mediaItem = $mediaClass::findOrFail($mediaItemId);

        if (!isset($mediaItem)) {
            return response()->json(['error' => 'media_item_not_found'], 400);
        }

        $this->fillMediaCustomProperties($mediaItem, $custom_properties);

        return response()->json([
            'success' => true,
            'message' => 'Custom properties updated successfully'
        ], 200);
    }

    /**
     * Updates a MediaItem.
     *
     * @param MediaRequest $request
     * @param mixed $mediaItemId
     * @return \Illuminate\Http\JsonResponse
     **/
    public function updateMediaItem(MediaRequest $request, $mediaItemId)
    {
        $mediaClass = config('media-library.media_model');
        $mediaItem = $mediaClass::findOrFail($mediaItemId);

        if (!isset($mediaItem)) return response()->json(['error' => 'media_item_not_found'], 400);

        $properties = $request->getCustomProperties();

        $this->fillMediaCustomProperties($mediaItem, $properties);

        // $mediaItem->save();
        return response()->json(['success' => true], 200);
    }

    /**
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     * @param array $properties
     */
    private function fillMediaCustomProperties($media, $properties)
    {
        // prevent overriding the custom properties set by other processes like generating conversions
        $media->refresh();

        foreach ($properties as $key => $value) {
            $mm_tag = Str::startsWith($key, 'mm_tag_');
            if ($mm_tag) {
                $media->$key = $value;
            } else {
                // Handle boolean values properly
                if ($value === 'true' || $value === '1') {
                    $value = true;
                } elseif ($value === 'false' || $value === '0') {
                    $value = false;
                }

                $media->setCustomProperty($key, $value);
            }
        }

        $media->save();
    }
}
