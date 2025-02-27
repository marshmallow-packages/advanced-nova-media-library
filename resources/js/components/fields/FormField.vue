<template>
  <component
    :is="field.fullSize ? 'FullWidthField' : 'DefaultField'"
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
  >
    <template #field>
      <div :class="{ 'px-6 md:px-8 pt-6': field.fullSize }">
        <Gallery
          ref="gallery"
          v-if="hasSetInitialValue"
          v-model="value"
          :editable="!field.readonly"
          :removable="field.removable"
          custom-properties
          :field="field"
          :multiple="field.multiple"
          :uploads-to-vapor="field.uploadsToVapor"
          @copy="copyUrl"
        />

        <span v-if="field.existingMedia">
          <Button
            dusk="cancel-update-button"
            variant="ghost"
            type="button"
            class="ml-2 mt-2"
            @click.prevent="existingMediaOpen = true"
          >
            {{ openExistingMediaLabel }}
          </Button>
          <ExistingMedia
            :open="existingMediaOpen"
            @close="existingMediaOpen = false"
            @select="addExistingItem"
            @copy="copyUrl"
          />
        </span>
      </div>
    </template>
  </component>
</template>

<script>
import { Button } from "laravel-nova-ui";
import { FormField, HandlesValidationErrors } from "laravel-nova";
import Vapor from "laravel-vapor";
import Gallery from "../Gallery";
import FullWidthField from "../FullWidthField";
import ExistingMedia from "../ExistingMedia";
import objectToFormData from "object-to-formdata";
import get from "lodash/get";

export default {
  mixins: [FormField, HandlesValidationErrors],
  components: {
    Gallery,
    FullWidthField,
    ExistingMedia,
    Button,
  },
  props: ["resourceName", "resourceId", "field"],
  data() {
    return {
      hasSetInitialValue: false,
      existingMediaOpen: false,
    };
  },
  computed: {
    openExistingMediaLabel() {
      const type = this.field.type === "media" ? "Media" : "File";

      if (this.field.multiple || this.value.length === 0) {
        return this.__(`Add Existing ${type}`);
      }

      return this.__(`Use Existing ${type}`);
    },
  },

  mounted() {
    this.field.fill = (formData) => {
      let attribute = this.field.attribute;

      this.value.forEach((file, index) => {
        let isNewImage = !file.id;
        let attributeString = "__media__[" + attribute + "][" + index + "]";

        if (file && isNewImage) {
          if (!file.isVaporUpload) {
            formData.append(attributeString, file.file, file.name);
          } else {
            let vaporFile = file.vaporFile;
            formData.append(attributeString + "[is_vapor_upload]", true);
            formData.append(attributeString + "[key]", vaporFile.key);
            formData.append(attributeString + "[uuid]", vaporFile.uuid);
            formData.append(
              attributeString + "[file_name]",
              vaporFile.filename
            );
            formData.append(
              attributeString + "[file_size]",
              vaporFile.file_size
            );
            formData.append(
              attributeString + "[mime_type]",
              vaporFile.mime_type
            );
          }
        } else {
          formData.append(attributeString, file.id);
        }

        if (isNewImage) {
          objectToFormData(
            {
              [`__media-custom-properties__[${attribute}][${index}]`]:
                this.getImageCustomProperties(file),
            },
            {},
            formData
          );
        }
      });
    };
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      let value = this.field.value || [];

      if (!this.field.multiple) {
        value = value.slice(0, 1);
      }

      this.value = value;
      this.hasSetInitialValue = true;
    },

    getImageCustomProperties(image) {
      return (this.field.customPropertiesFields || []).reduce(
        (properties, { attribute: property }) => {
          properties[property] = get(image, `custom_properties.${property}`);

          // Fixes checkbox problem
          if (properties[property] === true) {
            properties[property] = 1;
          }

          return properties;
        },
        {}
      );
    },

    /**
     * Update the field's internal value.
     */
    handleChange(value) {
      this.value = value;
    },

    copyUrl(image) {
      let image_src = false;
      if (image.__media_urls__.form) {
        image_src = image.__media_urls__.form;
      } else if (image.__media_urls__.detailView) {
        image_src = image.__media_urls__.detailView;
      } else {
        image_src = image.__media_urls__.__original__;
      }
      navigator.clipboard.writeText(image_src);
      Nova.success("Copied to clipboard");
    },

    addExistingItem(item) {
      // Copy to trigger watcher to recognize differnece between new and old values
      // https://github.com/vuejs/vue/issues/2164
      let copiedArray = this.value.slice(0);

      if (!this.field.multiple) {
        copiedArray.splice(0, 1);
      }

      copiedArray.push(item);
      this.value = copiedArray;
    },
  },
};
</script>
