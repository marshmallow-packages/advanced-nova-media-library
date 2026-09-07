<?php

namespace Marshmallow\AdvancedNovaMediaLibrary\Fields;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\MediaLibrary\Support\TemporaryDirectory;
use Spatie\MediaLibrary\MediaCollections\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @mixin Media
 */
trait HandlesExistingMediaTrait
{
    public function enableExistingMedia(): self
    {
        return $this->withMeta(['existingMedia' => (bool) config('nova-media-library.enable-existing-media')]);
    }

    /**
     * Reject a save that references media which cannot be resolved, BEFORE
     * removeDeletedMedia() gets a chance to delete anything.
     *
     * A submitted value can fail to resolve when the row was deleted after the
     * form was rendered, or when an empty value reached us as null via
     * ConvertEmptyStringsToNull. Such a value is also absent from the ids
     * removeDeletedMedia() keeps, so the media already in the collection is
     * deleted and the unresolvable reference adds nothing back - the field ends
     * up empty. Skipping quietly turns that into silent, permanent data loss:
     * it cost milesenergy.nl a product image and took their homepage down.
     *
     * Throwing here instead aborts the save inside Nova's transaction, so the
     * existing media survives and the user gets an actionable message rather
     * than a 500.
     */
    private function guardAgainstUnresolvableExistingMedia($data, Collection $medias, string $collection): void
    {
        $mediaClass = config('media-library.media_model');
        $addedMediaIds = $medias->pluck('id')->toArray();

        $unresolvable = collect($data)
            ->filter(fn ($value) => $this->isExistingMediaReference($value, $addedMediaIds))
            ->reject(fn ($mediaId) => $mediaClass::find($mediaId) instanceof $mediaClass);

        if ($unresolvable->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            $collection => __('This field references an image that no longer exists. Reload the page and try again.'),
        ]);
    }

    /**
     * New files arrive as UploadedFile objects and Vapor uploads as arrays;
     * anything else scalar that is not already attached is a reference to
     * existing media.
     */
    private function isExistingMediaReference($value, array $addedMediaIds): bool
    {
        return ! ($value instanceof UploadedFile)
            && ! is_array($value)
            && ! in_array($value, $addedMediaIds);
    }

    private function addExistingMedia(NovaRequest $request, $data, HasMedia $model, string $collection, Collection $medias): Collection
    {
        $addedMediaIds = $medias->pluck('id')->toArray();

        return collect($data)
            ->filter(fn ($value) => $this->isExistingMediaReference($value, $addedMediaIds))
            ->map(function ($mediaId, int $index) use ($request, $model, $collection) {
                $mediaClass = config('media-library.media_model');
                $existingMedia = $mediaClass::find($mediaId);

                if (! $existingMedia) {
                    return null;
                }

                // Mimic copy behaviour
                // See Spatie\MediaLibrary\Models\Media->copy()
                $temporaryDirectory = TemporaryDirectory::create();
                $temporaryFile = $temporaryDirectory->path($existingMedia->file_name);
                app(Filesystem::class)->copyFromMediaLibrary($existingMedia, $temporaryFile);
                $media = $model->addMedia($temporaryFile)->withCustomProperties($this->customProperties);

                if ($this->responsive) {
                    $media->withResponsiveImages();
                }

                if (! empty($this->customHeaders)) {
                    $media->addCustomHeaders($this->customHeaders);
                }

                $media = $media->toMediaCollection($collection);

                // fill custom properties for recently created media
                $this->fillMediaCustomPropertiesFromRequest($request, $media, $index, $collection);

                // Delete our temp collection
                $temporaryDirectory->delete();

                return $media->getKey();
            })
            ->filter(function ($mediaId) {
                return $mediaId !== null;
            });
    }
}
