<template>
  <Modal :show="image" @modal-close="onCancel" class="modal-cropper">
    <card
      class="text-center clipping-container max-w-view bg-white rounded-lg shadow-lg overflow-hidden"
    >
      <div class="p-4">
        <Cropper
          v-if="image"
          ref="clipper"
          class="anml-cropper-viewport"
          :stencil-props="configs || {}"
          :src="imageUrl"
          @ready="onImageReady"
          @error="onImageError"
        />
      </div>
      <div class="bg-30 px-6 py-3 footer rounded-lg">
        <Button
          type="button"
          variant="link"
          :label="__('Cancel')"
          @click.prevent="onCancel"
        />

        <Button
          type="button"
          variant="action"
          icon="arrow-uturn-left"
          :title="__('Rotate -90')"
          @click.prevent="rotate(-90)"
        />
        <Button
          type="button"
          variant="action"
          icon="arrow-uturn-right"
          :title="__('Rotate +90')"
          @click.prevent="rotate(+90)"
        />

        <Button
          ref="updateButton"
          type="button"
          variant="solid"
          :label="__('Update')"
          @click.prevent="onSave"
        />
      </div>
    </card>
  </Modal>
</template>

<script>
import Converter from "../converter";
import { Cropper } from "vue-advanced-cropper";
import "vue-advanced-cropper/dist/style.css";
import { Button } from "laravel-nova-ui";

export default {
  components: {
    Cropper,
    Button,
  },
  props: {
    image: Object,
    configs: {
      type: Object,
      default: () => ({}),
    },
    mustCrop: {
      type: Boolean,
      default: false,
    },
  },
  data: () => ({
    rotationHistory: 0,
  }),
  computed: {
    mime() {
      // if mime type is set on direct on the file it means it is an already existing image
      // in case  taking mime type from file it means the file has been just uploaded
      return this.image.mime_type || this.image.file.type;
    },
    imageUrl() {
      return this.image ? this.image.__media_urls__.__original__ : null;
    },
    cropAnyway() {
      return this.image.mustCrop === true && this.mustCrop;
    },
  },
  watch: {
    image: function (newValue) {
      if (newValue) {
        this.$nextTick(() => {
          this.focusUpdateButton();
        });
      }
      this.reset();
    },
  },
  methods: {
    focusUpdateButton() {
      const button = this.$refs.updateButton;

      if (!button) {
        return;
      }

      const element = button.$el || button;

      if (typeof element.focus === "function") {
        element.focus();
      }
    },
    reset() {
      if (this.$refs.clipper && this.image) {
        this.$refs.clipper.rotate(-this.rotationHistory);
      }
      this.rotationHistory = 0;
    },
    rotate(angle) {
      if (!this.$refs.clipper) {
        return;
      }

      this.$refs.clipper.rotate(angle);
      this.rotationHistory += angle;
    },
    onImageReady() {
      // Safari can lay the modal out before the image has dimensions, which
      // leaves the cropper collapsed to zero height. Recalculating once the
      // image is loaded fixes that.
      this.$nextTick(() => {
        if (this.$refs.clipper) {
          this.$refs.clipper.refresh();
        }
      });
    },
    onImageError() {
      Nova.error(
        this.__("The image could not be loaded for cropping. Please try again."),
      );
    },
    onSave() {
      let fileData = null;

      try {
        const { canvas } = this.$refs.clipper.getResult();
        const base64 = canvas.toDataURL(this.mime);
        const file = Converter(base64, this.mime, this.image.file_name);

        fileData = {
          file,
          __media_urls__: {
            __original__: base64,
            default: base64,
          },
          name: file.name,
          file_name: file.name,
        };
      } catch (error) {
        // Without this the modal silently stays open and the button looks dead.
        Nova.error(this.__("The image could not be cropped. Please try again."));
        console.error(error);

        return;
      }

      this.$emit("crop-completed", fileData);
      this.$emit("close");
    },
    onCancel() {
      // Under mustCrop the image was only added so it could be cropped, so
      // cancelling discards it instead of leaving an uncropped image behind.
      if (this.cropAnyway) {
        this.$emit("crop-cancelled", this.image);
      }

      this.$emit("close");
    },
  },
};
</script>

<style lang="scss" scoped>
.footer {
  display: flex;
  justify-content: space-between;
}

.modal-cropper {
  z-index: 400;
}

/* Safari has been seen collapsing the cropper to zero height, which hides the
   image and pushes the footer to the top of an apparently empty modal.
   The class is namespaced on purpose: `cropper-canvas` belongs to Cropper.js,
   whose global stylesheet sets `position: absolute` on it. Scoped styles do
   not protect against an unscoped global rule matching the same class. */
.anml-cropper-viewport {
  min-height: 20rem;
}

.max-w-view {
  max-width: calc(100vw - 6.5rem);
}

@media (min-aspect-ratio: 4/3) {
  .max-w-view {
    max-width: 60vw;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
