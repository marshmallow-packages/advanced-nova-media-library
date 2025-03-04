<template>
  <GalleryItem class="gallery-item-file">
    <div class="gallery-item-info px-2 py-2">
      <a
        class="mr-1 md:mr-2 hover:text-primary-500"
        :href="image.__media_urls__.__original__"
        target="_blank"
      >
        <Icon name="magnifying-glass" type="mini" />
      </a>
      <a
        v-if="downloadUrl"
        class="mr-2 hover:text-primary-500"
        :href="downloadUrl"
      >
        <Icon name="arrow-top-right-on-square" type="mini" />
      </a>
      <span class="label truncate">
        {{ image.file_name }}
      </span>

      <a
        v-if="isCustomPropertiesEditable"
        class="edit edit--file ml-1 md:ml-2 hover:text-primary-500"
        href="#"
        @click.prevent="$emit('edit-custom-properties', image)"
      >
        <Icon name="pencil" type="mini" />
      </a>
      <a
        v-if="removable"
        class="self-end text-red-600 ml-1 md:ml-2 hover:text-red-700"
        href="#"
        @click.prevent="$emit('remove')"
      >
        <Icon name="trash" type="mini" />
      </a>
    </div>
  </GalleryItem>
</template>

<script>
import GalleryItem from "./GalleryItem";
import { Icon } from "laravel-nova-ui";

export default {
  props: ["image", "removable", "isCustomPropertiesEditable"],
  components: {
    GalleryItem,
    Icon,
  },
  computed: {
    downloadUrl() {
      return this.image.id
        ? `/nova-vendor/marshmallow/advanced-nova-media-library/download/${this.image.id}`
        : null;
    },
  },
};
</script>

<style lang="scss">
$bg-color: #f5f6f7;
$border-color: #e2e8f0;

.gallery .edit.edit--file {
  position: relative;
  top: auto;
  right: auto;
}

.gallery-item-file {
  &.gallery-item {
    width: 100%;

    .gallery-item-info {
      display: flex;
      border-radius: 2px;
      border: 1px solid $bg-color;

      .label {
        flex-grow: 1;
      }

      .delete {
        align-self: flex-end;
      }
    }
  }
}
</style>
