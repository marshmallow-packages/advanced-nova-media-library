<template>
  <div class="gallery-item">
    <slot />
  </div>
</template>

<script>
export default {
  props: ["image", "thumbnail", "removable"],
};
</script>

<style lang="scss">
$bg-color: #f5f6f7;
$border-color: #e2e8f0;

.gallery {
  &.editable {
    .gallery-item {
      cursor: grab;
    }
  }

  .gallery-item {
    // float: left;
    display: flex;
    overflow-y: hidden;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    border: 1px solid $border-color;
    background-color: transparentize($bg-color, 0.2);

    .gallery-item-info {
      display: flex;
      background-color: transparentize($bg-color, 0.2);
      z-index: 10;
    }
  }
}
</style>
