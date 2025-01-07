<template>
  <div
    class="existing-media-item mx-2 border border-gray-200 dark:border-gray-700 group mb-4 relative cursor-pointer group hover:scale-105 transition-all"
  >
    <div class="overflow-hidden relative">
      <img
        v-if="'__media_urls__' in item && 'indexView' in item.__media_urls__"
        :src="item.__media_urls__.indexView"
        class="block w-full"
        style="
          height: 200px;
          object-fit: contain;
          background: #6c6c6c1a;
          border: 1px solid #6c6c6c0f;
        "
      />
      <div class="absolute top-0 left-0 mt-3 ml-3 hidden group-hover:block">
        <OutlineButton type="button" @click.prevent="$emit('copy')">{{
          __("Copy")
        }}</OutlineButton>
      </div>
      <div class="absolute top-0 right-0 mt-3 mr-3 hidden group-hover:block">
        <DefaultButton @click.prevent="$emit('select')" type="button">{{
          __("Select")
        }}</DefaultButton>
      </div>
    </div>
    <div class="p-3 px-2 py-2">
      <h4
        class="truncate h-4 mb-1 font-semibold"
        v-if="'name' in item"
        :title="item.name"
      >
        {{ item.name }}
      </h4>
      <div class="truncate text-gray-600 text-xs">
        <span class="dimensions">
          <strong>{{ item.mime_type }}</strong>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      default: function () {
        return {};
      },
      type: Object,
    },
  },
};
</script>

<style lang="scss">
.existing-media-item {
  &.float-left {
    float: left;
  }

  &.group:hover .group-hover\:block {
    display: block;
  }

  &.hover\:scale\-105:hover {
    transform: scale(1.05);
  }

  &.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
  }
}
</style>
