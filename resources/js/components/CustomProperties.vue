<template>
  <transition name="fade">
    <CustomPropertiesModal :fields="filledFields" @close="handleClose" @update="handleUpdate" />
  </transition>
</template>

<script>
import CustomPropertiesModal from "./CustomPropertiesModal";
import api from "../api";
import tap from "lodash/tap";
import get from "lodash/get";
import set from "lodash/set";
import clone from "lodash/clone";

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
      image: clone(this.modelValue),
    }
  },

  computed: {
    filledFields() {
      return JSON.parse(JSON.stringify(this.fields)).map(field => tap(field, field => {
        field.value = this.getProperty(field.attribute)
      }))
    }
  },

  methods: {
    handleClose() {
      this.$emit("close");
    },

    async handleUpdate(formData) {
      // Extract custom properties from form data
      const customProperties = {};
      for (let [property, value] of formData.entries()) {
        customProperties[property] = value;
        this.setProperty(property, value);
      }

      // If this is an existing media item (has an ID), save via API
      if (this.image.id) {
        try {
          await api.updateProperties(this.image, customProperties);
          Nova.success(this.__('Custom properties updated successfully'));
        } catch (error) {
          Nova.error(this.__('Failed to update custom properties'));
          console.error('Error updating custom properties:', error);
          return; // Don't emit update if API call failed
        }
      }

      this.$emit('update:modelValue', this.image);
      this.handleClose();
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
