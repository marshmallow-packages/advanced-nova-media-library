<template>
  <CustomPropertiesModal
    @close="handleClose"
    :fields="filledFields"
    @update="handleUpdate"
    @create="handleCreate"
    :newItem="newItem"
    :mediaModel="image"
  />
</template>

<script>
import CustomPropertiesModal from "./CustomPropertiesModal";
import api from "../api";
import tap from "lodash/tap";
import get from "lodash/get";
import set from "lodash/set";

export default {
  props: {
    modelValue: {
      type: Object,
      required: true,
    },
    fields: {
      type: Array,
      required: true,
    },
  },

  components: {
    CustomPropertiesModal,
  },

  data() {
    return {
      org_image: this.modelValue,
      image: JSON.parse(JSON.stringify(this.modelValue)),
      newItem: {
        media_id: this.modelValue.id,
      },
    };
  },

  computed: {
    newItemData() {
      return {
        customProperties: {
          ...this.newItem,
        },
        media_id: this.image.id,
      };
    },
    filledFields() {
      return JSON.parse(JSON.stringify(this.fields)).map((field) =>
        tap(field, (field) => {
          field.value = this.getProperty(field.attribute);
        })
      );
    },
  },

  methods: {
    resetNewItem() {
      this.errors = {};

      this.newItem = {
        media_id: null,
      };
    },

    handleClose() {
      this.$emit("close");
    },

    handleCreate(data) {
      for (let [property, value] of data.entries()) {
        this.setProperty(property, value);
      }

      let properties = this.image.custom_properties;
      this.org_image.custom_properties = properties;

      this.image = this.org_image;
      this.$emit("update:modelValue", this.image);

      this.handleClose();
    },

    handleUpdate(newItem) {
      let itemData = this.newItemData;

      if (itemData.media_id !== null) {
        itemData.customProperties.media_id = itemData.media_id;
      }

      Object.entries(newItem).forEach((property) => {
        const [propertyKey, propertyValue] = property;
        this.setProperty(propertyKey, propertyValue);
      });

      if (this.image.id) {
        this.updateMediaItem(newItem);
      }

      this.$emit("update:modelValue", this.image);

      this.handleClose();
    },

    async updateMediaItem(custom_properties) {
      this.loading = true;
      try {
        this.uploading = true;
        this.errors = {};
        await api.updateProperties(this.image, custom_properties);
        this.uploading = false;
        this.customPropertiesModalOpen = false;
        Nova.success(this.__("Media item updated"));
        this.resetNewItem();
      } catch (e) {
        this.uploading = false;
        this.handleErrors(e);
      }
      this.loading = false;
    },

    handleErrors(res) {
      let errors =
        res.response && res.response.data && res.response.data.errors;
      if (errors) {
        this.errors = errors;
        Object.values(errors).map((error) => Nova.error(error));
      }
    },

    getProperty(property) {
      return get(this.image, `custom_properties.${property}`);
    },

    setProperty(property, value) {
      set(this.image, `custom_properties.${property}`, value);
    },
  },
};
</script>
