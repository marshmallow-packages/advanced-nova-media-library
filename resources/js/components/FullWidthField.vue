<template>
  <FieldWrapper>
    <div class="py-6 w-full">
      <div class="px-6 md:px-8">
        <FormLabel
          :for="field.attribute"
          :class="{
            'mb-2': field.helpText && showHelpText,
          }"
        >
          {{ fieldLabel }}&nbsp;<span
            v-if="field.required"
            class="text-danger text-sm"
            >{{ __("*") }}</span
          >
        </FormLabel>

        <HelpText :show-help-text="showHelpText">
          {{ field.helpText }}
        </HelpText>
      </div>

      <slot name="field" />
    </div>
  </FieldWrapper>
</template>

<script>
// todo: extend from `default-field` somehow
export default {
  props: {
    field: { type: Object, required: true },
    fieldName: { type: String },
    showHelpText: { type: Boolean, default: true },
  },

  computed: {
    fieldLabel() {
      // If the field name is purposefully an empty string, then
      // let's show it as such
      if (this.fieldName === "") {
        return "";
      }

      return this.fieldName || this.field.singularLabel || this.field.name;
    },
  },
};
</script>
