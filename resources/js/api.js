export default {
  async update(mediaItemId, mediaItem) {
    return Nova.request().post(
      `/nova-vendor/marshmallow/advanced-nova-media-library/update-item/${mediaItemId}`,
      mediaItem
    );
  },
  async updateProperties(mediaItem, custom_properties) {
    return Nova.request().post(
      `/nova-vendor/marshmallow/advanced-nova-media-library/update-properties/${mediaItem.id}`,
      {
        _mediaId: mediaItem.id,
        _mediaData: mediaItem,
        _mediaProperties: custom_properties,
      }
    );
  },
};
