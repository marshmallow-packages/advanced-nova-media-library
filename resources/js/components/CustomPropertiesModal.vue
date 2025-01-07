<template>
  <Modal
    :show="true"
    maxWidth="2xl"
    @modal-close="handleClose"
    :classWhitelist="[
      'flatpickr-current-month',
      'flatpickr-next-month',
      'flatpickr-prev-month',
      'flatpickr-weekday',
      'flatpickr-weekdays',
      'flatpickr-calendar',
    ]"
  >
    <card class="overflow-hidden">
      <form
        class="rounded-lg shadow-lg overflow-hidden w-action-fields"
        @submit.prevent="handleUpdate"
        autocomplete="off"
      >
        <div v-for="field in fields" :key="field.attribute" class="action">
          <component :is="'form-' + field.component" :field="field" />
        </div>

        <div class="bg-30 px-6 py-3 flex">
          <div class="flex items-center ml-auto">
            <OutlineButton
              type="button"
              class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"
              @click.prevent="handleClose"
            >
              {{ __("Cancel") }}
            </OutlineButton>

            <DefaultButton type="submit" class="btn btn-default btn-primary">
              {{ __("Update") }}
            </DefaultButton>
          </div>
        </div>
      </form>
    </card>
  </Modal>
</template>

<script>
export default {
  props: {
    fields: {
      type: Array,
      required: true,
    },
    newItem: Object,
    mediaModel: Object,
  },

  methods: {
    handleClose() {
      this.$emit("close");
    },

    handleCreate() {
      let formData = new FormData();
      this.fields.forEach((field) => field.fill(formData));
      this.$emit("create", formData);
    },

    handleUpdate() {
      if (this.mediaModel.id == undefined) {
        return this.handleCreate();
      }

      this.fields.forEach((field) => {
        const formData = new FormData();
        field.fill(formData);

        const values = Array.from(formData.values());

        if (field.component === "trix-field") {
          this.newItem[field.attribute] = values[0];
          return;
        }

        // Is array
        const firstKey = Array.from(formData.keys())[0];
        if (firstKey && firstKey.endsWith("]")) {
          this.newItem[field.attribute] = values || [];
        } else {
          if (values.length === 0) this.newItem[field.attribute] = void 0;
          if (values.length === 1) this.newItem[field.attribute] = values[0];
          if (values.length > 1) this.newItem[field.attribute] = values;
        }
      });

      this.$emit("update", this.newItem);
    },
  },
};
</script>
