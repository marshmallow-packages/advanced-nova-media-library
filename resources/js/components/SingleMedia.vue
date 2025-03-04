<template>
  <GalleryItem
    class="gallery-item-image"
    :class="{ 'show-statistics': field.showStatistics }"
  >
    <div class="gallery-item-info p-3">
      <a
        v-if="downloadUrl"
        class="icon download hover:text-primary-500"
        :href="downloadUrl"
        title="Download"
      >
        <Icon name="arrow-top-right-on-square" type="mini" />
      </a>
      <div
        v-if="downloadUrl"
        class="icon copy hover:text-primary-500"
        @click.prevent="copyUrl"
        title="Copy"
      >
        <Icon name="clipboard" type="mini" />
      </div>
      <a
        v-if="removable"
        class="icon delete hover:text-primary-500"
        href="#"
        @click.prevent="$emit('remove')"
        title="Remove"
      >
        <Icon name="trash" type="mini" />
      </a>
      <a
        v-if="isCustomPropertiesEditable"
        class="icon edit hover:text-primary-500"
        href="#"
        @click.prevent="$emit('edit-custom-properties', image)"
        title="Edit custom properties"
      >
        <Icon name="pencil" type="mini" />
      </a>
      <a
        class="preview hover:text-primary-500"
        href="#"
        @click.prevent="showPreview"
      >
        <Icon name="magnifying-glass" type="mini" />
      </a>
      <a
        v-if="croppable"
        class="icon crop hover:text-primary-500"
        href="#"
        @click.prevent="$emit('crop-start', image)"
      >
        <Icon name="scissors" type="mini" />
      </a>
    </div>
    <img
      :src="src"
      :alt="image.name"
      ref="image"
      class="flex-1 gallery-image"
    />
    <div v-if="field.showStatistics" class="statistics mt-1 mb-1">
      <div v-if="image.name" class="mb-1 truncate">
        <strong>{{ image.name }}</strong>
      </div>
      <div v-if="size" class="size">
        <strong>{{ size }}</strong>
      </div>
      <div class="dimensions">
        <strong>{{ width }}×{{ height }}</strong> px
      </div>
      <div class="ratio">
        <strong>{{ aspectRatio }}</strong> (<i>{{ ratio }}</i
        >)
      </div>
      <div class="type">
        {{ mimeType }}
      </div>
    </div>
  </GalleryItem>
</template>

<script>
import GalleryItem from "./GalleryItem";
import { Icon } from "laravel-nova-ui";

export default {
  components: {
    GalleryItem,
    Icon,
  },
  props: [
    "image",
    "field",
    "removable",
    "editable",
    "isCustomPropertiesEditable",
  ],
  data: () => ({
    acceptedMimeTypes: ["image/jpg", "image/jpeg", "image/png"],
    src: "data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==",
    width: undefined,
    height: undefined,
    aspectRatio: undefined,
    ratio: undefined,
    size: undefined,
  }),
  computed: {
    downloadUrl() {
      return this.image.id
        ? `/nova-vendor/marshmallow/advanced-nova-media-library/download/${this.image.id}`
        : null;
    },
    croppable() {
      return (
        this.editable &&
        this.field.croppable &&
        this.acceptedMimeTypes.includes(this.mimeType)
      );
    },
    mimeType() {
      return this.image.mime_type || this.image.file.type;
    },
  },
  watch: {
    image: {
      handler: "getImage",
      immediate: true,
    },
  },
  methods: {
    showPreview() {
      const blobUrl = this.image.file
        ? URL.createObjectURL(this.image.file)
        : this.image.__media_urls__.preview;
      window.open(blobUrl, "_blank");
    },
    getImage() {
      if (this.editable && this.image.__media_urls__.form) {
        this.src = this.image.__media_urls__.form;
      } else if (!this.editable && this.image.__media_urls__.detailView) {
        this.src = this.image.__media_urls__.detailView;
      } else if (this.isVideo(this.image.__media_urls__.__original__)) {
        //Seconds to seek to, to get thumbnail of video
        let seconds = 1; //TODO get this from the field instead of hardcoding it here
        this.getVideoThumbnail(this.image.__media_urls__.__original__, seconds);
      } else {
        this.src = this.image.__media_urls__.__original__;
      }

      if (this.field.showStatistics) {
        setTimeout(this.calculateStatistics);
      }
    },
    copyUrl() {
      let image_src = this.src;
      navigator.clipboard.writeText(image_src);
      Nova.success("Copied to clipboard");
    },

    getVideoThumbnail(path, secs = 0) {
      const video = document.createElement("video");
      video.onloadedmetadata = () => {
        video.currentTime = Math.min(
          Math.max(0, (secs < 0 ? video.duration : 0) + secs),
          video.duration,
        );
      };
      video.onseeked = (e) => {
        const canvas = document.createElement("canvas");
        canvas.height = video.videoHeight;
        canvas.width = video.videoWidth;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        this.src = canvas.toDataURL();
      };
      video.src = path;
    },
    isVideo(mediaPath) {
      if (mediaPath.startsWith("data:video")) return true;
      //TODO better video detection
      const supportedExtensions = [".mp4", ".ogv", ".webm"];
      return supportedExtensions.some((suffix) => {
        return mediaPath.endsWith(suffix);
      });
    },
    calculateStatistics() {
      if (this.$refs.image.complete) {
        this.width = this.$refs.image.naturalWidth;
        this.height = this.$refs.image.naturalHeight;
        this.ratio = Math.round((this.width / this.height) * 100) / 100;

        const gcd = this.gcd(this.width, this.height);
        this.aspectRatio = this.width / gcd + ":" + this.height / gcd;

        if (this.field.showStatistics) {
          const src = this.$refs.image.currentSrc;

          if (src.startsWith("data:")) {
            const base64Length = src.length - (src.indexOf(",") + 1);
            const padding =
              src.charAt(src.length - 2) === "="
                ? 2
                : src.charAt(src.length - 1) === "="
                  ? 1
                  : 0;
            this.size = this.formatBytes(base64Length * 0.75 - padding);
          } else if (window.performance !== undefined) {
            const imgResourceTimings = window.performance.getEntriesByName(
              this.$refs.image.currentSrc,
            );
            if (imgResourceTimings.length) {
              const decodedBodySize = imgResourceTimings[0].decodedBodySize;
              if (decodedBodySize) {
                this.size = this.formatBytes(
                  imgResourceTimings[0].decodedBodySize,
                );
              } else {
                this.size = undefined;
              }
            } else {
              this.size = undefined;
            }
          }
        }
      } else {
        this.$refs.image.onload = this.calculateStatistics;
      }
    },
    gcd(a, b) {
      if (b === 0) {
        return a;
      }

      return this.gcd(b, a % b);
    },
    formatBytes(bytes, decimals = 2) {
      if (bytes === 0) return "0 Bytes";

      const k = 1024;
      const dm = decimals < 0 ? 0 : decimals;
      const sizes = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];

      const i = Math.floor(Math.log(bytes) / Math.log(k));

      return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
    },
  },
};
</script>

<style lang="scss">
$bg-color: #f5f6f7;
$border-color: #e2e8f0;
$item-max-size: 150px;

.gallery {
  .gallery-item-image.gallery-item {
    width: $item-max-size;
    height: $item-max-size;

    &:hover .gallery-item-info {
      display: flex;
    }

    &.show-statistics {
      padding-bottom: 6px;
      padding-bottom: 6px;
      height: #{$item-max-size + 50px};
    }

    .gallery-item-info {
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      border: 1px solid $border-color;
      background-color: transparentize($bg-color, 0.2);
      position: absolute;
      z-index: 10;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;

      .delete {
        right: 10px;
        color: rgb(var(--colors-red-500));
      }

      .crop {
        left: 10px;
        top: auto;
        bottom: 10px;
      }
    }

    .gallery-image {
      overflow-y: hidden;
      object-fit: cover;
      display: block;
      max-height: 100%;
    }

    .statistics,
    .type {
      // position: absolute;
      left: 0;
      width: 100%;
      font-size: 0.7rem;
      line-height: 0.95;
      text-align: center;
    }

    .statistics {
      bottom: 1px;

      .dimensions {
        font-size: 0.675rem;
      }

      .ratio {
        font-size: 0.6rem;
      }
    }

    .type {
      margin-top: 5px;
    }
  }

  .icon {
    cursor: pointer;
    position: absolute;
    top: 10px;
  }

  .edit {
    right: 30px;
  }

  .download {
    left: 10px;
  }

  .copy {
    left: 30px;
  }
}
</style>
