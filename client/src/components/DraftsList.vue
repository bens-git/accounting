<template>
  <v-container class="d-flex justify-center">
    <v-card title="Drafts" flat style="min-width: 90vw; min-height: 90vh">
      <template v-slot:text>
        <v-row>
          <!-- Search Field -->
          <v-col cols="12" md="3">
            <v-text-field
              density="compact"
              v-model="draftStore.search"
              label="Search"
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              hide-details
              single-line
              @input="debounceSearch"
            />
          </v-col>

          <!-- Type -->
          <v-col cols="12" md="2">
            <v-select
              density="compact"
              v-model="draftStore.selectedType"
              :items="draftStore.types"
              label="Type"
              :clearable="true"
              @update:modelValue="debounceSearch"
            />
          </v-col>

          <!-- Tag -->
          <v-col cols="12" md="2">
            <v-select
              density="compact"
              v-model="draftStore.selectedTag"
              :items="draftStore.tags"
              label="Tag"
              :clearable="true"
              @update:modelValue="debounceSearch"
            />
          </v-col>

          <!-- Recurrence Type -->
          <v-col cols="12" md="2">
            <v-select
              density="compact"
              v-model="draftStore.selectedRecurrenceType"
              :items="draftStore.recurrenceTypes"
              label="Recurrence Type"
              :clearable="true"
              @update:modelValue="debounceSearch"
            />
          </v-col>

          <!-- Create Button -->
          <v-col cols="12" md="3">
            <v-btn
              prepend-icon="mdi-plus"
              color="success"
              @click="[(isEdit = false), (showDraftDialog = true)]"
            >
              New
            </v-btn>

            <!-- Reset Button -->
            <v-btn
              prepend-icon="mdi-refresh"
              color="primary"
              @click="draftStore.resetFilters"
            >
              Reset
            </v-btn>
          </v-col>
        </v-row>
      </template>

      <v-data-table-server
        v-model:items-per-page="draftStore.itemsPerPage"
        :headers="headers"
        :items="draftStore.drafts"
        :items-length="draftStore.totalDrafts"
        loading-text="Loading... Please wait"
        :search="draftStore.search"
        item-value="id"
        @update:options="draftStore.updateOptions"
        mobile-breakpoint="sm"
        fixed-header
        :height="'65vh'"
      >
        <template v-slot:[`item.actions`]="{ item }">
          <v-btn
            icon
            @click="
              [
                (draftStore.selectedDraft = item),
                (isEdit = true),
                (showDraftDialog = true),
              ]
            "
            v-if="userStore.user"
          >
            <v-icon>mdi-pencil</v-icon>
          </v-btn>

          <v-btn icon @click="deleteDraft(item)" v-if="userStore.user">
            <v-icon>mdi-delete</v-icon>
          </v-btn>

          <v-btn icon @click="goToLogin" v-else>
            <v-icon>mdi-login</v-icon>
          </v-btn>
        </template>

        <template v-slot:[`item.recurrence_start_month`]="{ item }">
          {{ getMonthName(item.recurrence_start_month) }}
        </template>
      </v-data-table-server>
    </v-card>
  </v-container>

  <!-- Creation / Modification Modal -->
  <v-dialog v-model="showDraftDialog" :persistent="false" class="custom-dialog">
    <draft-form
      :isEdit="isEdit"
      :draft="draftStore.selectedDraft"
      @close-modal="showDraftDialog = false"
    />
  </v-dialog>

  <!-- Deletion Modal -->
  <v-dialog
    v-model="showDeletionDialog"
    :persistent="false"
    class="custom-dialog"
  >
    <delete-draft-form
      :isEdit="false"
      @close-modal="showDeletionDialog = false"
    />
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useDraftStore } from "@/stores/draft";
import { useUserStore } from "@/stores/user";
import _ from "lodash";
import DeleteDraftForm from "./DeleteDraftForm.vue";
import DraftForm from "./DraftForm.vue";
import { useRouter } from "vue-router";

const draftStore = useDraftStore();
const userStore = useUserStore();
const router = useRouter();

const years = generateYears();

function getMonthName(month) {
  // Adjust for 0-indexed months in JavaScript Date
  const date = new Date(2000, month - 1);
  return new Intl.DateTimeFormat("en-US", { month: "long" }).format(date);
}

function generateYears() {
  const currentYear = new Date().getFullYear();
  const range = 20; // Adjust the range of years as needed
  return Array.from({ length: range }, (_, i) => currentYear - 10 + i);
}

const showDraftDialog = ref(false);
const showDeletionDialog = ref(false);

const headers = [
  {
    title: "Actions",
    align: "start",
    sortable: false,
    key: "actions",
  },
  {
    title: "Draft",
    align: "start",
    sortable: true,
    key: "name",
  },
  {
    title: "Type",
    align: "start",
    sortable: true,
    key: "type",
  },
  {
    title: "Tag",
    align: "start",
    sortable: false,
    key: "tag",
  },
  {
    title: "Party",
    align: "start",
    sortable: false,
    key: "party.name",
  },
  {
    title: "User",
    align: "start",
    sortable: false,
    key: "user.name",
  },
  {
    title: "Recipient",
    align: "start",
    sortable: false,
    key: "recipient.name",
  },
  {
    title: "Amount",
    align: "start",
    sortable: true,
    key: "amount",
  },
  {
    title: "Recurrence Type",
    align: "start",
    sortable: true,
    key: "recurrence_type",
  },
  {
    title: "Recurrence Start Month",
    align: "start",
    sortable: true,
    key: "recurrence_start_month",
  },
];

const debounceSearch = _.debounce(() => {
  draftStore.fetchDrafts();
}, 300);

const editDraft = (template) => {
  draftStore.selectedDraft = template;
  dialog.value = true;
};

const deleteDraft = (template) => {
  draftStore.selectedDraft = template;
  showDeletionDialog.value = true;
};

const goToLogin = () => {
  router.push({ name: "login-form" }); // Adjust the route name as necessary
};

onMounted(async () => {
  await draftStore.fetchRecurrenceTypes();
  await draftStore.fetchTags();
});
</script>

<style>
.custom-dialog .v-overlay__content {
  pointer-events: none;
}

.custom-dialog .v-card {
  pointer-events: auto;
}
</style>
